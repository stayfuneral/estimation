<?php

class PluginEstimationFacade
{
    const TICKET_USER_TYPE_AUTHOR = 1;
    const TICKET_STATUS_CLOSED = 6;

    private $request;
    private $ticket;
    private $estimationTicket;
    private $ticketUser;
    private $user;
    private $estimation;

    public function __construct($request)
    {
        $this->request =  $request;

        $this->ticket = new Ticket;
        $this->ticketUser = new Ticket_User;
        $this->user = new User;
        $this->estimation = new PluginEstimationEstimation;
        $this->estimationTicket = new PluginEstimationTicket;
    }

    public function parseRequest()
    {
        $response = [];

        $ticketId = (int) $this->request->ticketId;

        if(!$ticketId || $ticketId <= 0) {
            throw new Exception('ticket id not defined');
        }

        $this->estimationTicket->setTicketId($ticketId);

        if(!$this->estimation->checkEstimationByTicket($ticketId)) {
            $response = $this->addEstimation();
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function checkEstimation()
    {
        $checkEstimation = $this->estimation->checkEstimationByTicket($this->request->ticketId);
        $author = $this->estimationTicket->getTicketUser(1);

        $ticketId = (int) $this->request->ticketId;

        $response = [];

        if(!$checkEstimation) {
            return [
                'result' => 'empty',
                'data' => $this->estimationTicket->getPrintableData()
            ];
        }

        $response['data'] = $this->estimation->getPrintableDataById($ticketId);

        $isAuto = (bool) $checkEstimation['is_auto'];

        switch ($isAuto) {
            case true:
                $response['result'] = 'auto';
                $response['comment'] = $author.', по Вашей заявке уже поставлена оценка';
                break;
            case false:
                $response['result'] = 'found';
                $response['comment'] = $author. ', Вы уже ставили оценку по данной заявке!';
                break;
        }

        return $response;
    }

    public function addEstimation()
    {
        $response = [];
        $ticketId = (int) $this->request->ticketId;

        $estimationParams = [
            'ticket_id' => $ticketId,
            'estimation' => (bool) $this->request->estimation,
            'group_id' => $this->estimationTicket->getTicketGroup()
        ];

        switch ($this->request->estimation) {
            case true:
                $response['comment'] = $this->estimationTicket->getTicketUser(1) .
                    ', спасибо за оценку!';
                break;
            case false:
                $response['comment'] = $this->estimationTicket->getTicketUser(1) .
                    ', нам очень жаль, что наши специалисты не оправдали ваших ожиданий, мы приложим усилия для исправления ситуации!';
                break;
        }

        if(!empty($this->request->comment) && is_array($this->request->comment)) {

            switch (count($this->request->comment)) {
                case 1:
                    $comment = htmlspecialchars($this->request->comment[0]);
                    break;
                default:
                    $comment = json_encode($this->request->comment, JSON_UNESCAPED_UNICODE);
            }

            $estimationParams['comment'] = $comment;
        }

        $ticket = $this->ticket->find([
            'id' => $ticketId,
            'status' => self::TICKET_STATUS_CLOSED
        ]);

        if(!empty($ticket)) {
            $ticketUsers = $this->estimationTicket->getTicketUsers();
            $estimationParams['author_id'] = $ticketUsers['author'];
            $estimationParams['responsible_id'] = $ticketUsers['responsible'];
        }

        $add = $this->estimation->add($estimationParams);

        if($add) {
            $response['result'] = 'success';
        }

        return $response;
    }

    public function init()
    {
        $response = [];
        $estimationParams = [];

        $ticketId = (int) $this->request->ticketId;

        if(!$ticketId || $ticketId <= 0) {
            throw new Exception('ticket id not defined');
        }

        $estimationParams['tickets_id'] = $ticketId;
        $estimationParams['estimation'] = htmlspecialchars($this->request->estimation);

        if(!empty($this->request->comment) && is_array($this->request->comment)) {

            switch (count($this->request->comment)) {
                case 1:
                    $comment = htmlspecialchars($this->request->comment[0]);
                    break;
                default:
                    $comment = json_encode($this->request->comment, JSON_UNESCAPED_UNICODE);
            }

            $estimationParams['comment'] = $comment;

        }

        $ticket = $this->ticket->find([
            'id' => $ticketId,
            'status' => self::TICKET_STATUS_CLOSED
        ]);

        if(!empty($ticket)) {
            $ticketUser = $this->ticketUser->find([
                'tickets_id' => $ticketId,
                'type' => self::TICKET_USER_TYPE_AUTHOR
            ]);

            foreach ($ticketUser as $id => $item) {
                $estimationParams['users_id'] = (int) $item['users_id'];
            }

            $user = $this->user->find([
                'id' => $estimationParams['users_id']
            ]);

            foreach ($user as $id => $author) {
                $response['user'] = $author['firstname'] . ' ' . $author['realname'];
            }

            $estimation = $this->estimation->find([
                'tickets_id' => $ticketId,
                'users_id' => $estimationParams['users_id']
            ]);

            if(!$this->estimation->find(['tickets_id' => $ticketId])) {
                if(!$estimation) {
                    $add = $this->estimation->add($estimationParams);

                    if($add) {
                        $response['result'] = 'success';
                        $response['add_estimation'] = $add;
                    }
                }
            }


        } else {
            $response['result'] = 'duplicate';
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);

    }
}