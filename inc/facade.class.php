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
                $response['comment'] = 'Спасибо за оценку!';
                break;
            case false:
                $response['comment'] = 'Спасибо за обратную связь! Ваша оценка поможет сделать ИТ сервис лучше';
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
}