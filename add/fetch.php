<?php

include '../../../inc/includes.php';
include '../../../kint.phar';

$request = json_decode(file_get_contents('php://input'));

Plugin::load('estimation', true);

$facade = new PluginEstimationFacade($request);
$facade->parseRequest();

//$response = [];
//if(!$request->ticketId || $request->ticketId === 0) {
//    $response = [
//        'result' => 'error',
//        'error_description' => 'ticket id not set'
//    ];
//} else {
//    $response['result'] = 'success';
//
//    $estimationParams = [
//        'tickets_id' => (int) $request->ticketId,
//        'estimation' => htmlspecialchars($request->estimation)
//    ];
//
//
//    $ticket = new Ticket;
//    $ticketUser = new Ticket_User;
//
//    foreach ($ticketUser->find(['tickets_id' => $ticketId, 'type' => 1]) as $item) {
//        $userId = (int) $item['users_id'];
//
//        $estimationParams['users_id'] = $userId;
//
//        $user = new User;
//
//        $ticketAuthor = $user->find(['id' => $userId]);
//
//        $response['user'] = $ticketAuthor[$userId]['firstname'] . ' ' . $ticketAuthor[$userId]['realname'];
//    }
//}
//
//
//
//echo json_encode($response, JSON_UNESCAPED_UNICODE);