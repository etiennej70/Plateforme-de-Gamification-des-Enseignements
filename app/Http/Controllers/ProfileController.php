<?php

namespace App\Http\Controllers;

use App\Achievement;
use App\GameHistory;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\ProfileQuestion;
use App\ProfileAnswer;
use App\User;
use Input;
use DB;

class ProfileController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questions = ProfileQuestion::join('profiles_answers', 'profiles_questions.id', '=', 'profiles_answers.profile_question_id')
            ->where('profiles_answers.user_id', Auth::user()->id)
            ->get();

        return view('profile.index', ['questions' => $questions]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function about()
    {
        $questions = ProfileQuestion::join('profiles_answers', 'profiles_questions.id', '=', 'profiles_answers.profile_question_id')
            ->where('profiles_answers.user_id', Auth::user()->id)
            ->get();

        return view('profile.about', ['questions' => $questions]);
    }



    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $input = Input::except('_token');

        foreach ($input as $key => $value) {

            $tag = ProfileQuestion::where('tag', $key)->firstOrFail();
            $id  = $tag->id;
            $answer = ProfileAnswer::where('profile_question_id', $id)->where('user_id', Auth::user()->id)->firstOrFail();
            $answer->answer = $value;
            $answer->save();
        }
        return Redirect::back()->with('success', 'Profile updated!');
    }

    public function uploadPicture(Request $request){
        try{
            $user = User::find(Auth::user()->id);
            $file = $request->file('avatar');

            if ($file) {

                if ($_FILES['avatar']['error'] > 0) return Redirect::back()->with('error', 'An error has occured');

                $extensions_valides = array('jpg', 'png', 'jpeg', 'gif');
                $extension_upload = strtolower(substr(strrchr($_FILES['avatar']['name'], '.') , 1));

                if (!in_array($extension_upload, $extensions_valides));

                $uniq = $this->uniqString(40);
                $fileName = $uniq.'.'.$extension_upload;
                $fileName = (string)$fileName;

                $destination = "img/avatars/";

                $file->move($destination, $fileName);

                $user->avatar = $fileName;
                $user->save();
            }

        }catch(\Exception $e){
            return Redirect::back()->with('error', 'An error has occured');
        }
        return Redirect::back()->with('success', 'Profile picture updated!');
    }

    protected function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        }while ($rnd >= $range);

        return $min + $rnd;
    }


    protected function uniqString($length) {
        $_SESSION['token']="";
        unset($_SESSION['token']);
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        for($i = 0; $i < $length; $i++){
            $token .= $codeAlphabet[$this->crypto_rand_secure(0,strlen($codeAlphabet))];
        }
        return $token;
    }

    /**
     * Return the view achievements with all the users games informations
     */
    public function achievements()
    {
        $user = User::find(Auth::id());

        $topranking = DB::table('users')
                            ->orderBy('points', 'desc')
                            ->where('points', '>', $user->points)
                            ->take(2)
                            ->get();

        $lowranking = DB::table('users')
                            ->orderBy('points', 'desc')
                            ->where('points', '<', $user->points)
                            ->take(2)
                            ->get();

        $games = GameHistory::with('game')
                            ->orderBy('created_at', 'desc')
                            ->where('user_id', '=', $user->id)
                            ->take(5)
                            ->get();

        $badges = Auth::user()->achievements()->get();


        return view('profile/achievements', compact('user', 'topranking', 'lowranking', 'games', 'badges'));
    }
}