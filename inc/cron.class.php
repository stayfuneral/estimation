<?php

/**
 * Class PluginEstimationCron Автоматические действия
 */

class PluginEstimationCron extends CommonDBTM
{
    const CRON_ACTION_NAME = 'Estimation';

    public static function cronInfo($name)
    {
        switch ($name) {
            case 'Estimation':
                return [
                    'description' => 'Автоматическая оценка качества работы по заявке',
                ];
        }

        return [];
    }

    /**
     * Автоматическое выставление оценки качества работы специалиста
     *
     * @return array
     * @throws Exception
     */
    public static function cronEstimation()
    {
        global $DB;

        $result = [];

        $configs = new PluginEstimationConfigs;
        $ticket = new Ticket;
        $estimation = new PluginEstimationEstimation;
        $pluginTicket = new PluginEstimationTicket;
        $date = new DateTime();

        $closeDateYesterday = $date->sub(new DateInterval('P1D'))->format('Y-m-d');
        $closeDateBeforeYesterday = $date->sub(new DateInterval('P2D'))->format('Y-m-d');


        $closedTickets = $ticket->find([
            'status' => PluginEstimationTicket::TICKET_STATUS_CLOSED,
            [
                'AND' => [
                    'closedate' => ['>', $closeDateBeforeYesterday]
                ]
            ],
            [
                'AND' => [
                    'closedate' => ['<', $closeDateYesterday]
                ]
            ]
        ]);

        foreach ($closedTickets as $id => $closedTicket) {

            $result['ticket_'.$id] = [
                'ticket' => $closedTicket
            ];

            if(empty($estimation->find(['ticket_id' => $id]))) {

                $pluginInstallDate = $configs->getConfigs('install_date');

                if($closedTicket['closedate'] <= $pluginInstallDate) {
                    continue;
                }

                $pluginTicket->setTicketId($id);
                $ticketUsers = $pluginTicket->getTicketUsers();

                $estimationParams = [
                    'ticket_id' => $id,
                    'author_id' => $ticketUsers['author'],
                    'responsible_id' => $ticketUsers['responsible'],
                    'group_id' => $pluginTicket->getTicketGroup(),
                    'estimation' => true,
                    'is_auto' => true
                ];

                $result['ticket_'.$id]['estimation_params'] = $estimationParams;
                $result['ticket_'.$id]['add_estimation'] = $estimation->add($estimationParams);
            }


        }

        return $result;
    }

    /**
     * Проверка задания в БД
     *
     * @param CronTask $cronTask
     *
     * @return bool
     */
    public static function checkCronTask(CronTask $cronTask)
    {
        return $cronTask->getFromDBbyName(__CLASS__, self::CRON_ACTION_NAME);
    }



}