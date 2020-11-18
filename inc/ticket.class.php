<?php

class PluginEstimationTicket
{
    const TICKET_TYPE_AUTHOR = 1;
    const TICKET_TYPE_RESPONSIBLE = 2;
    const TICKET_STATUS_CLOSED = 6;

    private $ticketId;
    private $ticket;
    private $ticketUser;
    private $ticketGroup;
    private $user;
    private $group;

    /**
     * PluginEstimationTicket constructor.
     */
    public function __construct()
    {
        $this->ticket = new Ticket;
        $this->ticketUser = new Ticket_User;
        $this->user = new User;
        $this->ticketGroup = new Group_Ticket;
        $this->group = new Group;
    }

    /**
     * @param $ticketId
     */
    public function setTicketId($ticketId)
    {
        $this->ticketId = $ticketId;
    }

    /**
     * @return string
     */
    public function getTicketName()
    {
        $ticket = $this->getTicket();
        return $ticket['name'];
    }

    public function getTicket()
    {
        foreach ($this->ticket->find(['id' => $this->ticketId]) as $item) {
            return $item;
        }
    }


    /**
     * @return int
     */
    public function getTicketGroup()
    {
        $ticketGroup = $this->ticketGroup->find([
            'tickets_id' => $this->ticketId,
            'type' => self::TICKET_TYPE_RESPONSIBLE
        ]);

        foreach ($ticketGroup as $group) {
            return (int) $group['groups_id'];
        }
    }

    public function getTicketGroupName()
    {
        $groupId = $this->getTicketGroup();

        foreach ($this->group->find(['id' => $groupId]) as $item) {
            return $item['name'];
        }


    }

    /**
     * @return array
     */
    public function getTicketUsers()
    {
        $users = [];

        $ticketUsers = $this->ticketUser->find([
            'tickets_id' => $this->ticketId
        ]);

        foreach ($ticketUsers as $user) {
            switch ($user['type']) {
                case self::TICKET_TYPE_AUTHOR:
                    $users['author'] = (int) $user['users_id'];
                    break;
                case self::TICKET_TYPE_RESPONSIBLE:
                    $users['responsible'] = (int) $user['users_id'];
                    break;
            }
        }

        return $users;
    }

    /**
     * @param int $type
     *
     * @return string
     */
    public function getTicketUser($type)
    {
        $ticket = $this->ticketUser->find([
            'tickets_id' => $this->ticketId,
            'type' => $type
        ]);

        foreach ($ticket as $item) {
            $userId = (int) $item['users_id'];

            foreach ($this->user->find(['id' => $userId]) as $user) {
                
                return $user['firstname'] . ' ' . $user['realname'];
                
            }
        }
    }



    public function getPrintableData()
    {
        $ticketUsers = $this->getTicketUsers();
        $ticket = $this->getTicket();

        return [
            'ticket' => [
                'id' => $this->ticketId,
                'name' => $this->getTicketName()
            ],
            'author' => [
                'id' => $ticketUsers['author'],
                'name' => $this->getTicketUser(self::TICKET_TYPE_AUTHOR)
            ],
            'responsible' => [
                'id' => $ticketUsers['responsible'],
                'name' => $this->getTicketUser(self::TICKET_TYPE_RESPONSIBLE)
            ],
            'group' => [
                'id' => $this->getTicketGroup(),
                'name' => $this->getTicketGroupName()
            ],
            'closedate' => date('d.m.Y', strtotime($ticket['closedate']))
        ];
    }

}