<?php

namespace App\Http\Controllers;

use App\Model\Users;
use App\Model\Settings;
use App\Model\GameLol;
use App\Model\UsersChoices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Hash;
use Illuminate\Support\Facades\Validator;
use Log;
use Intervention\Image\ImageManager;

class UserController extends Controller
{

    public function login(Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');
        $token = $request->input('token');

        $user = Users::where('email', $email)->first();
        if ($user) {
            if (Hash::check($password, $user->password)) {

                if ($token == '' or $token = 'undefined') {
                    $token = 0;
                }
                $user->token = $token;
                $user->save();

                $userg = GameLol::where('mid', $user->id)->first();

                return response()->json([
                    'success' => true,
                    'mid' => $user->id,
                    'name' => $user->name,
                    'bio' => $user->bio,
                    'resume' => $user->resume,
                    'age' => $user->age,
                    'type' => ''. $user->type .'',
                    'picture' => $user->picture,
                    'state' => $user->state,
                    'summon' => $userg->summon,
                    'elo' => ''. $userg->elo .'',
                    'rprimary' => ''. $userg->rprimary .'',
                    'rsecond' => ''. $userg->rsecond .'',
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Email ou senha está errado',
                    'type_error' => 99
                ]);

            }
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Email ou senha está errado',
                'type_error' => 99
            ]);
        } 

    }

    public function register(Request $request) {

        // USER DATA
        $name = $request->input('name');
        $token = $request->input('token');
        $email = $request->input('email');
        $password = $request->input('password');
        $picture = $request->input('picture');
        $state = $request->input('state');
        $bio = $request->input('bio');
        $resume = $request->input('resume');
        $type = $request->input('type');
        $age = $request->input('age');

        $validator = Validator::make(
                    [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'state' => $state,
                    'type' => $type,
                    'age' => $age,
                    ],
                    [
                    'name' => 'required',
                    'email' => 'required',
                    'password' => 'required',
                    'state' => 'required',
                    'type' => 'required',
                    'age' => 'required'
                    ]
        );


        if ($validator->fails()) {
            $error_messages = $validator->messages();
            return response()->json([
                    'success' => false,
                    'error' => "Você não enviou todos os campos necessários da sua informação pessoal",
                    'fields' => $error_messages,
                    'type_error' => 1
                ]);

        } else {
            if (Users::where('email', $email)->count() == 0) {
                // REGISTER USER
                $user = new Users;

                $resume_c = $resume;
                if ($resume_c == 'undefined') {
                    $resume_c = '';
                }

                $bio_c = $bio;
                if ($bio_c == 'undefined') {
                    $bio_c = '';
                }
                
                $user->name = $name;
                $user->email = $email;
                $user->password = Hash::make($password);
                $user->age = $age;
                $user->token = $token;
                $user->state = $state;
                $user->type = $type;
                $user->bio = $bio_c;
                $user->resume = $resume_c;
                $user->is_active = 1;
                $user->lock_choice = date('Y-m-d H:i:s');
                $user->save();


                if ($picture and $picture != "undefined" and $picture != "") {
                    if (substr($picture, 0, 4) == 'data'){

                        $userp = Users::find($user->id);

                        $settings = Settings::where('key_setting', 'url_root')->first();
                        $url = $settings->key_value;
                        $image = $request->input('picture');  // your base64 encoded
                        $image = str_replace('data:image/jpeg;base64,', '', $image);
                        $image = str_replace(' ', '+', $image);
                        $imageName = $userp->id .'.'.'jpg';
                        \File::put(public_path() . '/storage/' . $imageName, base64_decode($image));

                        $new_file = $url . '/storage/'. $imageName;

                        $userp->picture = $new_file;
                        $userp->save();

                        }
                }

                $game = new GameLol;
                $game->elo = 1;
                $game->summon = "";
                $game->rprimary = 1;
                $game->rsecond = 1;
                $game->mid = $user->id;
                $game->save();
                


                return response()->json([
                    'success' => true,
                    'mid' => $user->id,
                    'picture' => $user->picture,
                ]);

            } else {

                return response()->json([
                    'success' => false,
                    'error' => "Email já cadastrado em nossa plataforma.",
                    'type_error' => 99
                ]);
            }
        }

    }

    public function updateProfile(Request $request) {

        // USER DATA
        $name = $request->input('name');
        $token = $request->input('token');
        $picture = $request->input('picture');
        $state = $request->input('state');
        $bio = $request->input('bio');
        $resume = $request->input('resume');
        $type = $request->input('type');
        $mid = $request->input('mid');
        $age = $request->input('age');

        $validator = Validator::make(
                    [
                    'mid' => $mid,
                    'name' => $name,
                    'state' => $state,
                    'type' => $type,
                    'age' => $age,
                    ],
                    [
                    'mid' => 'required',
                    'name' => 'required',
                    'state' => 'required',
                    'type' => 'required',
                    'age' => 'required'
                    ]
        );


        if ($validator->fails()) {
            $error_messages = $validator->messages();
            return response()->json([
                    'success' => false,
                    'error' => "Você não enviou todos os campos necessários da sua informação pessoal",
                    'fields' => $error_messages,
                    'type_error' => 1
                ]);

        } else {

                $user = Users::where('id', $mid)->first();
                if ($user) {
                    if ($request->input('picture')) {
                        if (substr($request->input('picture'), 0, 4) == 'data'){
                            $settings = Settings::where('key_setting', 'url_root')->first();
                            $url = $settings->key_value;
                            $image = $request->input('picture');  // your base64 encoded
                            $image = str_replace('data:image/jpeg;base64,', '', $image);
                            $image = str_replace(' ', '+', $image);
                            $imageName = $user->id .'.'.'jpg';
                            \File::put(public_path() . '/storage/' . $imageName, base64_decode($image));

                            $new_file = $url . '/storage/'. $imageName;

                            $user->picture = $new_file;
                        }
                    }

                    
                    $user->name = $name;
                    $user->age = $age;
                    $user->token = $token;
                    $user->state = $state;
                    $user->type = $type;
                    $user->bio = $bio;
                    $user->resume = $resume;
                    $user->save();
                    return response()->json([
                        'success' => true,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Usuário não encontrado',
                        'type_error' => 99
                    ]);
                }
            }

    }

    public function registerGame(Request $request) {



        $mid = $request->input('mid');
        $elo = $request->input('elo');
        $summon = $request->input('summon');
        $rprimary = $request->input('rprimary');
        $rsecond = $request->input('rsecond');

            $validator = Validator::make(
                    [
                    'mid' => $elo,
                    'elo' => $elo,
                    'summon' => $summon,
                    'rprimary' => $rprimary,
                    'rsecond' => $rsecond,
                    ],
                    [
                    'mid' => 'required',
                    'elo' => 'required',
                    'summon' => 'required',
                    'rprimary' => 'required',
                    'rsecond' => 'required',
                    ]
            );


            if  ($validator->fails()) {
                $error_messages = $validator->messages();
                return response()->json([
                        'success' => false,
                        'error' => "Você não enviou todos os campos necessários da sua conta do lol",
                        'fields' => $error_messages,
                        'type_error' => 1
                    ]);

            } else {
                // REGISTER USER
                $user = Users::where('id', $mid)->first();
                if ($user) {
                    $user->is_active = 1;
                    $user->save();

                    // REGISTER GAME
                    $game = GameLol::where('mid', $mid)->first();
                    $game->elo = $elo;
                    $game->summon = $summon;
                    $game->rprimary = $rprimary;
                    $game->rsecond = $rsecond;
                    $game->mid = $mid;
                    $game->save();


                    return response()->json([
                        'success' => true,
                    ]);

                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Usuário não encontrado',
                        'type_error' => 99
                    ]);
                }
            }
        

    }

    public function updateGame(Request $request) {

        $mid = $request->input('mid');
        $elo = $request->input('elo');
        $summon = $request->input('summon');
        $rprimary = $request->input('rprimary');
        $rsecond = $request->input('rsecond');

            $validator = Validator::make(
                    [
                    'mid' => $elo,
                    'elo' => $elo,
                    'summon' => $summon,
                    'rprimary' => $rprimary,
                    'rsecond' => $rsecond,
                    ],
                    [
                    'mid' => 'required',
                    'elo' => 'required',
                    'summon' => 'required',
                    'rprimary' => 'required',
                    'rsecond' => 'required',
                    ]
            );


            if  ($validator->fails()) {
                $error_messages = $validator->messages();
                return response()->json([
                        'success' => false,
                        'error' => "Você não enviou todos os campos necessários da sua conta do lol",
                        'fields' => $error_messages,
                        'type_error' => 1
                    ]);

            } else {
                // REGISTER USER
                $game = GameLol::where('mid', $mid)->first();
                if ($game) {

                    $game->elo = $elo;
                    $game->summon = $summon;
                    $game->rprimary = $rprimary;
                    $game->rsecond = $rsecond;
                    $game->mid = $mid;
                    $game->save();


                    return response()->json([
                        'success' => true,
                    ]);

                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Jogo não encontrado',
                        'type_error' => 99
                    ]);
                }
            }

    }

    public function choices(Request $request, $mid) {
        $rprimary = $request->input('rprimary');
        if ($rprimary == 'undefined' or $rprimary == 'null') {
            $rprimary = '';
        }
        $type = $request->input('type');
        if ($type == 'undefined' or $type == 'null') {
            $type = '';
        }
        $elomax = $request->input('elomax');
        if ($elomax == 'undefined' or $elomax == 'null') {
            $elomax = '';
        }
        $elomin = $request->input('elomin');
        if ($elomin == 'undefined' or $elomin == 'null') {
            $elomin = '';
        }


        $user = Users::where('id', $mid)->first();
        if ($user) {

            if ($user->count_lock == 10) {
                $date1 = date("Y-m-d H:i:s");
                $date2 = date ("Y-m-d H:i:s", strtotime($user->lock_choice));

                if ($date1 < $date2) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Você já fez suas escolhas do dia, aguarde o tempo para voltar a fazer.',
                        'type_error' => 1,
                        'time_block' => date ("d-m-Y H:i", strtotime($user->lock_choice))
                    ]);
                } else {
                    $user->count_lock = 0;
                    $user->save();
                }
            }

            if ($elomin and $elomax and $type and $rprimary) {
                $choices = Users::leftJoin('game_lol','users.id','=','game_lol.mid')
                        ->select('users.*', 'game_lol.summon', 'game_lol.elo', 'game_lol.rprimary', 'game_lol.rsecond')
                        ->whereNotExists(function($query) use ($mid)
                        {
                            $query->select(DB::raw(1))
                                  ->from('users_choices')
                                  ->whereRaw('users_choices.mid_duo = users.id')
                                  ->whereRaw('users_choices.status > 0')
                                  ->whereRaw('users_choices.mid = "'. $mid .'"');
                        })
                        ->where('users.id', '!=', $mid)
                        ->where('game_lol.elo', '>=', $elomin)
                        ->where('game_lol.elo', '<=', $elomax)
                        ->where('users.type', $type)
                        ->where('game_lol.rprimary', $rprimary)
                        ->where('users.is_active', 1)
                        ->orderBy('users.updated_at')
                        ->first();

            } else if ($type and $elomin and $elomax) {
                $choices = Users::leftJoin('game_lol','users.id','=','game_lol.mid')
                        ->select('users.*', 'game_lol.summon', 'game_lol.elo', 'game_lol.rprimary', 'game_lol.rsecond')
                        ->whereNotExists(function($query) use ($mid)
                        {
                            $query->select(DB::raw(1))
                                  ->from('users_choices')
                                  ->whereRaw('users_choices.mid_duo = users.id')
                                  ->whereRaw('users_choices.status > 0')
                                  ->whereRaw('users_choices.mid = "'. $mid .'"');
                        })
                        ->where('users.id', '!=', $mid)
                        ->where('game_lol.elo', '>=', $elomin)
                        ->where('game_lol.elo', '<=', $elomax)
                        ->where('users.type', $type)
                        ->where('users.is_active', 1)
                        ->orderBy('users.updated_at')
                        ->first();

            } else if ($rprimary and $elomin and $elomax) {
                $choices = Users::leftJoin('game_lol','users.id','=','game_lol.mid')
                        ->select('users.*', 'game_lol.summon', 'game_lol.elo', 'game_lol.rprimary', 'game_lol.rsecond')
                        ->whereNotExists(function($query) use ($mid)
                        {
                            $query->select(DB::raw(1))
                                  ->from('users_choices')
                                  ->whereRaw('users_choices.mid_duo = users.id')
                                  ->whereRaw('users_choices.status > 0')
                                  ->whereRaw('users_choices.mid = "'. $mid .'"');
                        })
                        ->where('users.id', '!=', $mid)
                        ->where('game_lol.elo', '>=', $elomin)
                        ->where('game_lol.elo', '<=', $elomax)
                        ->where('game_lol.rprimary', $rprimary)
                        ->where('users.is_active', 1)
                        ->orderBy('users.updated_at')
                        ->first();

            } else if ($rprimary and $type) {
                $choices = Users::leftJoin('game_lol','users.id','=','game_lol.mid')
                        ->select('users.*', 'game_lol.summon', 'game_lol.elo', 'game_lol.rprimary', 'game_lol.rsecond')
                        ->whereNotExists(function($query) use ($mid)
                        {
                            $query->select(DB::raw(1))
                                  ->from('users_choices')
                                  ->whereRaw('users_choices.mid_duo = users.id')
                                  ->whereRaw('users_choices.status > 0')
                                  ->whereRaw('users_choices.mid = "'. $mid .'"');
                        })
                        ->where('users.id', '!=', $mid)
                        ->where('users.type', $type)
                        ->where('game_lol.rprimary', $rprimary)
                        ->where('users.is_active', 1)
                        ->orderBy('users.updated_at')
                        ->first();

            } else if ($elomin and $elomax) {
                $choices = Users::leftJoin('game_lol','users.id','=','game_lol.mid')
                        ->select('users.*', 'game_lol.summon', 'game_lol.elo', 'game_lol.rprimary', 'game_lol.rsecond')
                        ->whereNotExists(function($query) use ($mid)
                        {
                            $query->select(DB::raw(1))
                                  ->from('users_choices')
                                  ->whereRaw('users_choices.mid_duo = users.id')
                                  ->whereRaw('users_choices.status > 0')
                                  ->whereRaw('users_choices.mid = "'. $mid .'"');
                        })
                        ->where('users.id', '!=', $mid)
                        ->where('game_lol.elo', '>=', $elomin)
                        ->where('game_lol.elo', '<=', $elomax)
                        ->where('users.is_active', 1)
                        ->orderBy('users.updated_at')
                        ->first();

            } else if ($type) {
                $choices = Users::leftJoin('game_lol','users.id','=','game_lol.mid')
                        ->select('users.*', 'game_lol.summon', 'game_lol.elo', 'game_lol.rprimary', 'game_lol.rsecond')
                        ->whereNotExists(function($query) use ($mid)
                        {
                            $query->select(DB::raw(1))
                                  ->from('users_choices')
                                  ->whereRaw('users_choices.mid_duo = users.id')
                                  ->whereRaw('users_choices.status > 0')
                                  ->whereRaw('users_choices.mid = "'. $mid .'"');
                        })
                        ->where('users.id', '!=', $mid)
                        ->where('users.type', $type)
                        ->where('users.is_active', 1)
                        ->orderBy('users.updated_at')
                        ->first();

            } else if ($rprimary) {
                $choices = Users::leftJoin('game_lol','users.id','=','game_lol.mid')
                        ->select('users.*', 'game_lol.summon', 'game_lol.elo', 'game_lol.rprimary', 'game_lol.rsecond')
                        ->whereNotExists(function($query) use ($mid)
                        {
                            $query->select(DB::raw(1))
                                  ->from('users_choices')
                                  ->whereRaw('users_choices.mid_duo = users.id')
                                  ->whereRaw('users_choices.status > 0')
                                  ->whereRaw('users_choices.mid = "'. $mid .'"');
                        })
                        ->where('users.id', '!=', $mid)
                        ->where('game_lol.rprimary', $rprimary)
                        ->where('users.is_active', 1)
                        ->orderBy('users.updated_at')
                        ->first();

            } else {
                $choices = Users::leftJoin('game_lol','users.id','=','game_lol.mid')
                        ->select('users.*', 'game_lol.summon', 'game_lol.elo', 'game_lol.rprimary', 'game_lol.rsecond')
                        ->whereNotExists(function($query) use ($mid)
                        {
                            $query->select(DB::raw(1))
                                  ->from('users_choices')
                                  ->whereRaw('users_choices.mid_duo = users.id')
                                  ->whereRaw('users_choices.status > 0')
                                  ->whereRaw('users_choices.mid = "'. $mid .'"');
                        })
                        ->where('users.id', '!=', $mid)
                        ->where('users.is_active', 1)
                        ->orderBy('users.updated_at')
                        ->first();

            }


            if ($choices) {

                // verificar se já está na lista de aceitou ou recusar
                $choice_m = UsersChoices::where('mid', $mid)
                                ->where('has_match', 0)
                                ->where('mid_duo', $choices->id)
                                ->first();

                if (!$choice_m) {

                    // inserindo na lista
                    $choice_n = new UsersChoices;
                    $choice_n->mid = $mid;
                    $choice_n->mid_duo = $choices->id;
                    $choice_n->has_match = 0;
                    $choice_n->save();

                    $choice_id = $choice_n->id;
                } else {
                    $choice_id = $choice_m->id;
                }

                $resume_c = $choices->resume;
                if ($resume_c == 'undefined') {
                    $resume_c = '';
                }

                $bio_c = $choices->bio;
                if ($bio_c == 'undefined') {
                    $bio_c = '';
                }

                $me = Users::find($mid);

                return response()->json([
                    'success' => true,
                    'id' => $choice_id,
                    'name' => $choices->name,
                    'bio' => $bio_c,
                    'resume' => $resume_c,
                    'age' => $choices->age,
                    'type' => $choices->type,
                    'picture' => $choices->picture,
                    'user_pic' => $me->picture,
                    'state' => $choices->state,
                    'elo' => $choices->elo,
                    'rprimary' => $choices->rprimary,
                    'rsecond' => $choices->rsecond,
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Infelizmente não temos novas escolhas para você hoje! Volte amanhã.',
                    'type_error' => 2
                ]);
            }

        } else {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não encontrado',
                'type_error' => 99
            ]);
        }

    }

    public function choice(Request $request, $mid, $id) {

        $status = $request->input('status');

        $validator = Validator::make(
                    [
                    'status' => $status,
                    ],
                    [
                    'status' => 'required',
                    ]
        );


        if ($validator->fails()) {
            $error_messages = $validator->messages();
            return response()->json([
                    'success' => false,
                    'error' => "Você não enviou todos os campos necessários para atualizar essa escolha",
                    'fields' => $error_messages,
                    'type_error' => 1
                ]);

        } else {

            $user = Users::where('id', $mid)->first();

            if ($user) {

                if ($user->count_lock == 10) {
                    $date1 = date("Y-m-d H:i:s");
                    $date2 = date ("Y-m-d H:i:s", strtotime($user->lock_choice));

                    if ($date1 < $date2) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Você já fez suas escolhas do dia, aguarde o tempo para voltar a fazer.',
                            'type_error' => 1,
                            'time_block' => date ("Y-m-d H:i:s", strtotime($user->lock_choice))
                        ]);
                    } else {
                        $user->count_lock = 0;
                        $user->save();
                    }
                }

                $choice = UsersChoices::where('id', $id)->first();

                if ($choice) {
                    if ($choice->status > 0) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Infelizmente ocorreu algum erro. Sua escolha já foi atualizada, reinicie o aplicativo.',
                            'type_error' => 99
                        ]);
                    } else {
                        $has_match = 0;

                        // ver se alguém me deu match
                        $s_choice = UsersChoices::where('mid_duo', $mid)->first();

                        if ($s_choice) {
                            if ($s_choice->status == 1 and $status == 1) {
                                $has_match = 1;
                                $choice->has_match = 1;
                                $s_choice->has_match = 1;
                                $s_choice->save();

                                $user_n = Users::find($s_choice->mid);

                                $msg = array(
                                    'msg_type' => 2,
                                    'title' => "Novo Duo!", 
                                    'body' => "Você acabou de realizar um duo com ". $user->name . " acesse seus duos!",
                                    'icon' => $user->picture,
                                );

                                send_push($user_n->token, $msg);
                            }
                        }
                        $choice->status = $status;
                        $choice->save();

                        $total_lock = $user->count_lock + 1;
                        $user->count_lock = $user->count_lock + 1;

                        if ($total_lock == 10) {
                            $user->lock_choice = date ("Y-m-d H:i:s", strtotime('+ 1 day'));
                        }
                        $user->save();

                        return response()->json([
                            'success' => true,
                            'has_match' => $has_match
                        ]);

                    }

                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Infelizmente ocorreu algum erro. Sua escolha não foi encontrada. Reinicie o aplicativo.',
                        'type_error' => 99
                    ]);
                }

            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não encontrado',
                    'type_error' => 99
                ]);
            }

        }
    }

    public function matchs(Request $request, $mid) {
        $user = Users::where('id', $mid)->first();

        if ($user) {

            $listquery = UsersChoices::leftJoin('users','users_choices.mid','=','users.id')
                                ->leftJoin('game_lol','users.id','=','game_lol.mid')
                                ->select('users.name', 'users.picture', 'users.id', 'game_lol.summon', 'users_choices.updated_at')
                                ->where('users_choices.mid_duo', $mid)
                                ->where('users_choices.has_match', 1)
                                ->orderBy('users_choices.updated_at', 'desc')
                                ->get();

            if ($listquery) {

                $list_data = array();

                    foreach ($listquery as $key) {
                        $listquery = array();
                        $listquery['id'] = $key->id;
                        $listquery['name'] = $key->name;
                        $listquery['picture'] = $key->picture;
                        $listquery['summon'] = $key->summon;
                        $listquery['updated_at'] = date('d/m/Y', strtotime($key->updated_at));

                        array_push($list_data, $listquery);
                    }


                    return response()->json([
                        'success' => true,
                        'list_data' => $list_data,
                    ]);

            } else {

                return response()->json([
                    'success' => false,
                    'error' => 'Não há resultados',
                    'type_error' => 1
                ]);

            }

        } else {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não encontrado',
                'type_error' => 99
            ]);
        }
    }

    public function teste() {
       $user_n = Users::find(13);

        $msg = array(
            'msg_type' => 2,
            'title' => "Novo duo!", 
            'body' => "Você acabou de realizar um duo com ". $user_n->name . " acesse seus duos!",
            'icon' => $user_n->picture,
        );

        send_push("ec05rClbyeA:APA91bH5fpBo-AcXN75EupfwW4nd3sHKUk25j-WoimQbrl5rIOgLRVRqJm16rLZvWHWjviBFKJHZy-BVMnriy_m2GRCvD4dsc3r5lrxCkYPG30G1LJubdXb-r2UeX1EDCBANBoYoH8bP", $msg);
    }

    public function nothing() {
        echo 'nada';
    }

}