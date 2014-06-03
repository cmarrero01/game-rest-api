<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Games Controller
 *
 * @package	CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 */
require APPPATH . '/libraries/REST_Controller.php';

class Games extends REST_Controller {

    public function __construct() {
        parent:: __construct();
        $this->load->model('games_model');
        $this->load->model('Tournaments_model');
        $this->load->library('Cod4stats');   //libreria para parsear COD4
    }

    /**
     * Devuelve una lista de juegos cargados en el sistema
     */
    function games_get() {
        $games = $this->games_model->games();  //devuelve true o false dependiendo si esta activo, o desactivado

        if (!empty($games)) {
            $this->response($games, 200);
        } else {
            $this->response(array('error' => 'No games'), 404);
        }
    }

    /**
     * <sumary>
     * Servicio que representa un slider determinado segun los parametros que se le pasen.
     * </sumary>
     * @param skliderId
     * */
    public function gameSlider_get() {
        //devuelve true o false dependiendo si esta activo, o desactivado
        $slider = $this->games_model->gamesSlider($this->get('sliderId'));
        // Si el slider existe
        if (!empty($slider)) {
            // Images.
            $images = $this->games_model->gamesSliderImages($this->get('sliderId'));
            // Devuelvo el slider y sus imagenes
            $response = array('response' => 'true', 'result' => array('slider' => $slider, 'images' => $images));
            $this->response($response, 200);
        } else {
            $this->response(array('response' => 'false', 'result' => 'Error, no slider'), 404);
        }
    }

    /**
     * <sumary>
     * Devuelve las caracteristicas del juego, listas de mejores jugadores, etc.
     * </sumary>
     * @param gameId
     * */
    public function game_get() {
        //devuelve true o false dependiendo si esta activo, o desactivado
        $game = $this->games_model->game($this->get('gameId'));

        if (!empty($game)) {
            $response = array('response' => 'true', 'result' => $game);
            $this->response($response, 200);
        } else {
            $this->response(array('response' => 'false', 'result' => 'Error, the game doesn\'t exist'), 404);
        }
    }

    /**
     * <sumary>
     * Devuelve una lista de torneos segun el filtro
     * </sumary>
     * @param gameId
     * */
    function gameTournaments_get() {
        $this->load->model('Tournaments_model');

        $tournaments = $this->games_model->game_tournaments($this->get('gameId')); //devuelve tru o false dependiendo si esta activo, o desactivado

        foreach ($tournaments as $tournament) {
            if($tournament->gameMode == 3){
                $group1 = $this->Tournaments_model->isTeamTournamentFull($tournament->tournamentId, '1');
                $group2 = $this->Tournaments_model->isTeamTournamentFull($tournament->tournamentId, '2');

                $tournament->usersInside = $group1->registered + $group2->registered;
            }else{
                $usersInside = $this->Tournaments_model->get_users_inscripted($tournament->tournamentId);
                if (!empty($usersInside)) {
                    $tournament->usersInside = count($usersInside);
                } else {
                    $tournament->usersInside = 0;
                }
            }
        }

        if (!empty($tournaments)) {
            $response = array('response' => true, 'result' => $tournaments);
            $this->response($response, 200);
        } else {
            $this->response(array('response' => false, 'result' => 'Error, no game tournaments'), 404);
        }
    }

    /**
     * <sumary>
     * Devuelve un solo torneo dependediendo del id
     * </sumary>
     * @param tournamentId
     * */
    function gameTournament_get() {

        $tournament = $this->games_model->game_tournament($this->get('tournamentId'));

        if (!empty($tournament)) {
            $response = array('response' => 'true', 'result' => $tournament);
            $this->response($response, 200);
        } else {
            $this->response(array('response' => 'false', 'result' => 'Error, no game tournament'), 404);
        }
    }

    /**
     * Toma el archivo de estadisticas del juego, lo parsea para que sean legibles y los guarda en la BD.
     * El archivo que recibe es mandado por la aplicacion de escritorio y pertenece al de un usuario en particular.
     * @params userId, gameId, statsFile
     */
    function gameGetStatsFromUser_post() {
        //Recibo el arhivo Log enviado por POST
        $img_file = $_FILES['archivo']['tmp_name'];
        $tmp_name = $_FILES['archivo']['tmp_name'];
        $file_name = $_FILES['archivo']['name'];

        //Guardo el archivo
        move_uploaded_file($tmp_name, $_SERVER['DOCUMENT_ROOT'] . "/restest/logs/" . $file_name);

        //Configuro los datos para el parseo
        $dataServer = array(
            'ID' => 2,
            'Name' => 'server cod4',
            'IP' => '127.0.0.1',
            'Port' => '8080',
            'Description' => '',
            'ModName' => "",
            'AdminName' => "",
            'ClanName' => "",
            'AdminEmail' => "",
            'GameLogLocation' => $_SERVER['DOCUMENT_ROOT'] . "/restest/logs/" . $file_name,
            'LastLogLine' => 0,
            'LastLogLineChecksum' => 0,
            'PlayedSeconds' => 0,
            'ServerEnabled' => 1,
            'ParsingEnabled' => 1,
            'ftppath' => "",
            'LastUpdate' => "1353271982",
            'ServerLogo' => "",
            'FTPPassiveMode' => 0,
        );

        //devuelve true si parseo con exito
        $status = $this->cod4stats->parse_stats($dataServer);
        //falta cmabiar donde se guardan los datos parseados...

        if ($status) {
            //Una vez parseado el archivo lo elimino
            unlink($_SERVER['DOCUMENT_ROOT'] . "/restest/logs/" . $file_name);
            $this->response($status, 200);
        } else {
            $this->response(array('error' => 'Error parsing'), 404);
        }
    }

    /**
     * Recibe el juego y el server del que debe recuperar las estadisticas,
     * parsearlas y guardarlas en la base de datos legibles.
     * @param gameId, serverId
     */
    function gameGetStatsFromServer_post() {

    }

    /**
     * Devuelve el estado de un juego.
     */
    function gameStatus_get() {
        $status = $this->games_model->get_status($this->get('gameId'));

        $this->response($status);
    }

}

