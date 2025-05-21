<?php

namespace App\Controllers;

use App\Core\Controller;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    public function index()
    {
        $client = new Client(['base_uri' => 'http://localhost/lms-api/api/public/api/']);

        try {
            $response = $client->get('welcome');
            $body = json_decode($response->getBody(), true);
            $message = $body['message'] ?? 'Welcome!';
        } catch (\Exception $e) {
            $message = 'API unreachable: ' . $e->getMessage();
        }

        $this->view('home', ['message' => $message]);
    }
}
