<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Category;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Episode;
use App\Models\LinkMovie;
use App\Models\Film;
use Carbon\Carbon;
class LeechMovieController extends Controller
{
    public function leech_movie(){
        $resp = Http::get("https://ophim1.com/danh-sach/phim-moi-cap-nhat?page=1")->json();
        // dd($resp);
        return view('admincp.leech.index', compact('resp'));
    }

    public function leech_detail($slug){
        $resp = Http::get("https://ophim1.com/phim/".$slug)->json();
        $resp_movie[] = $resp['movie'];
        // dd($resp_movie);
        return view('admincp.leech.leech_detail', compact('resp_movie'));
    }

    public function watch_leech_detail(Request $request){ 
        $slug = $request->slug;

        $resp = Http::get('https://ophim1.com/phim/'.$slug)->json();
        $resp_array[] = $resp['movie'];

        $output['content_title'] = '<h3 style="text-align: center;text-transform: uppercase;">'.$resp['movie']['name'].'</h3>';

    	$output['content_detail'] = '
            <div class="row">
                <div class="col-md-5"><img src="'.$resp['movie']['thumb_url'].'" width="100%"></div>
                <div class="col-md-7">
                    <h5><b>Tên phim :</b>'.$resp['movie']['name'].'</h5>
                    <p><b>Tên tiếng anh:'.$resp['movie']['origin_name'].'</b></p>
                    <p><b>Trạng thái :</b> '.$resp['movie']['episode_current'].'</p>
                    <p><b>Số tập :</b> '.$resp['movie']['episode_total'].'</p>
                    <p><b>Thời lượng : </b>'.$resp['movie']['time'].'</p>
                    <p><b>Năm phát hành : </b>'.$resp['movie']['year'].'</p>
                    <p><b>Chất lượng : </b>'.$resp['movie']['quality'].'</p>
                    <p><b>Ngôn ngữ : </b>'.$resp['movie']['lang'].'</p>';
                    foreach($resp['movie']['director'] as $dir){
                        $output['content_detail'].='Đạo diễn: <span class="badge badge-pill badge-info">'.$dir.'</span><br>';
                    }
                    $output['content_detail'].='<b>Thể loại :</b>';

                    foreach($resp['movie']['category'] as $cate){
                        $output['content_detail'].='
                        <p><span class="badge badge-pill badge-info">'.$cate['name'].'</span></p>';
                    }
                    $output['content_detail'].='<b>Diễn viên :</b>';
                    foreach($resp['movie']['actor'] as $act){
                        $output['content_detail'].='
                        <p><span class="badge badge-pill badge-info">'.$act.'</span></p>';
                    }
                    $output['content_detail'].='<b>Quốc gia :</b>';
                    foreach($resp['movie']['country'] as $country){
                        $output['content_detail'].='
                        <p><span class="badge badge-pill badge-info">'.$country['name'].'</span></p>';
                    }
                    $output['content_detail'].='

                </div>
            </div>
        ';

    	echo json_encode($output);
    }

    public function watch_leech_detail_episode(Request $request){
        $slug = $request->slug;

        $resp = Http::get('https://ophim1.com/phim/'.$slug)->json();
        // $resp_array[] = $resp['movie'];

        $output['content_episode'] = '<h3 style="text-align: center;text-transform: uppercase;">'.$resp['movie']['name'].'</h3>';

    	$output['content_detail_episode'] = '
            <div class="row">
                <div class="col-md-6">
                Phụ đề
                ';
                foreach($resp['episodes'] as $key => $res){
                    foreach($res['server_data'] as $key => $server_1){
                        $output['content_detail_episode'].= '<p> Tập '.$server_1['name'].'</p>
                        <p><input type="text" class="form-control" value="'.$server_1['link_embed'].'"></p>
                        <button class="btn btn-default btn-sm"> Thêm tập </button>
                        ';
                    }
                    $output['content_detail_episode'].='</div>';

                    $output['content_detail_episode'].='<div class="col-md-6">
                    Thuyết minh
                    ';
                    foreach($res['server_data'] as $key => $server_2){
                        $output['content_detail_episode'].= '<p> Tập '.$server_2['name'].'</p>
                        <p><input type="text" class="form-control" value="'.$server_2['link_embed'].'"></p>
                        <button class="btn btn-default btn-sm"> Thêm tập </button>
                        ';
                    }
                    $output['content_detail_episode'].='</div>';
            }
                
                
               
    	echo json_encode($output);
    }

    public function leech_episodes($slug){
        $resp = Http::get("https://ophim1.com/phim/".$slug)->json();
        // dd($resp);
        return view('admincp.leech.leech_episodes', compact('resp'));
    }

    public function leech_episodes_store(Request $request, $slug){
        $movie = Movie::where('slug', $slug)->first();
        $resp = Http::get("https://ophim1.com/phim/".$slug)->json();
        foreach($resp['episodes'] as $key => $res){
            foreach($res['server_data'] as $key_data => $res_data){
                $ep = new Episode();
                $ep->movie_id = $movie->id;
                $ep->linkphim = '<p><iframe allowfullscreen frameborder="0" height="360" scrolling="0" src="'.$res_data['link_embed'].'" width="100%"></iframe></p>';
                $ep->episode = $res_data['name'];
                if($key_data == 0){
                    $linkmovie = LinkMovie::orderBy('id','DESC')->first();
                    $ep->server = $linkmovie->id;
                }else{
                    $linkmovie = LinkMovie::orderBy('id','ASC')->first();
                    $ep->server = $linkmovie->id;
                }
                $ep->created_at = Carbon::now('Asia/Ho_Chi_Minh');
                $ep->updated_at = Carbon::now('Asia/Ho_Chi_Minh');
                $ep->save();
            }
        }
        return redirect()->back();
    }

    public function leech_store(Request $request, $slug){
        $resp = Http::get("https://ophim1.com/phim/".$slug)->json();
        $resp_movie[] = $resp['movie'];
        $movie = new Movie();
        foreach($resp_movie as $key => $res){
            $movie->title = $res['name'];
            $movie->trailer = $res['trailer_url'];

            $movie->sotap = $res['episode_total'];
            $movie->tags = $res['name'].','.$res['slug'];
            $movie->thoiluong = $res['time'];
            $movie->phude = 0;
            $movie->resolution = 0;         
            $movie->slug = $res['slug'];
            $movie->name_eng = $res['origin_name'];
            $movie->phim_hot = 1;
            $movie->description = $res['content'];
            $movie->status = 0;
            $movie->thuocphim = 0;

            $category = Category::orderBy('id', 'DESC')->first();
            $movie->category_id = $category->id;
           
            $country = Country::orderBy('id', 'DESC')->first();
            $movie->country_id = $country->id;

            $genre = Genre::orderBy('id', 'DESC')->first();
            $movie->genre_id = $genre->id;
            
            $movie->count_views = rand(100,99999);
            $movie->ngaytao = Carbon::now('Asia/Ho_Chi_Minh');
            $movie->ngaycapnhat = Carbon::now('Asia/Ho_Chi_Minh');

            $movie->image = $res['thumb_url'];
            $movie->save();

            $movie->movie_genre()->attach($genre);
        }
        // dd($resp_movie);
        return redirect()->back();
    }
}
