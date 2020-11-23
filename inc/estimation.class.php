<?php

/**
 * Class PluginEstimationEstimation
 */

class PluginEstimationEstimation extends CommonDBTM
{
    /**
     * @var int оценка "плохо"
     */
    const ESTIMATION_BAD = 0;
    /**
     * @var int оценка "хорошо"
     */
    const ESTIMATION_GOOD = 1;

    const ESTIMATION_TYPE_AUTO = 1;
    const ESTIMATION_TYPE_HUMAN = 0;

    /**
     * Список оценок
     *
     * @return array
     */
    public static function getEstimations()
    {
        return [
            self::ESTIMATION_BAD => 'Плохо',
            self::ESTIMATION_GOOD => 'Хорошо'
        ];
    }

    public static function getDependencies()
    {
        return [
            'ticket' => new Ticket,
            'group' => new Group,
            'user' => new User,
            'estimations' => self::getEstimations(),
            'types' => self::getEstimationTypes()
        ];
    }

    public static function getEstimationTypes()
    {
        return [
            self::ESTIMATION_TYPE_AUTO => 'Автоматический',
            self::ESTIMATION_TYPE_HUMAN => 'Пользовательский'
        ];
    }

    /**
     * Проверка оценки по ID заявки
     *
     * @param $ticketId ID заявки
     *
     * @return array
     */
    public function checkEstimationByTicket($ticketId)
    {
        $result = false;
        foreach ($this->find(['ticket_id' => $ticketId]) as $item) {
            if(!is_null($item)) {
                $result = $item;
            }
        }
        return $result;
    }

    /**
     * Данные для вывода в браузер
     *
     * @return array
     */
    public function getPrintableData()
    {
        $data = [];

        foreach ($this->find() as $item) {

            $estimationId = (int) $item['id'];
            $ticketId = (int) $item['ticket_id'];

            $ticket = new PluginEstimationTicket();
            $ticket->setTicketId($item['ticket_id']);
            $estimations = self::getEstimations();
            $estimationType = self::getEstimationTypes();
            $date = date('d.m.Y', strtotime($item['date']));

            $data[$estimationId] = [
                'id' => $estimationId,
                'ticket' => [
                    'id' => $ticketId,
                    'name' => $ticket->getTicketName()
                ],
                'author' => [
                    'id' => (int) $item['author_id'],
                    'name' => $ticket->getTicketUser($ticket::TICKET_TYPE_AUTHOR)
                ],
                'responsible' => [
                    'id' => (int) $item['responsible_id'],
                    'name' => $ticket->getTicketUser($ticket::TICKET_TYPE_RESPONSIBLE)
                ],
                'group' => [
                    'id' => (int) $item['group_id'],
                    'name' => $ticket->getTicketGroupName()
                ],
                'estimation' => $estimations[$item['estimation']],
                'type' => $estimationType[$item['is_auto']],
                'date' => $date
            ];

            if(!is_null($item['comment'])) {
                $comment = json_decode($item['comment'], true);

                if(!is_null($comment)) {
                    $data[$estimationId]['comment'] = implode(PHP_EOL, $comment);
                } else {
                    $data[$estimationId]['comment'] = $item['comment'];
                }
            }

        }

        return $data;


    }

    public function getPrintableDataById($id)
    {
        $estimations = $this->getPrintableData();

        foreach ($estimations as $eId => $eData) {
            if($id === $eData['ticket']['id']) {
                return $eData;
            }
        }
    }

    public function getTableFields()
    {
        global $DB;
        $fields = [];

        foreach ($DB->request("desc " . self::getTable()) as $item) {
            $fields[] = $item['Field'];
        }

        return $fields;
    }

    public function getFieldNames()
    {
        return [
            'id' => 'ID',
            'ticket_id' => 'Заявка',
            'author_id' => 'Инициатор запроса',
            'responsible_id' => 'Специалист',
            'group_id' => 'Группа специалистов',
            'estimation' => 'Оценка',
            'is_auto' => 'Тип оценки',
            'comment' => 'Комментарий',
            'date' => 'Дата оценки'
        ];
    }



    public function rawSearchOptions()
    {
        $table = self::getTable();
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Characteristics')
        ];
        $i = 0;
        foreach ($this->getFieldNames() as $field => $name) {

            $item = [
                'id' => $i++,
                'table' => $table,
                'field' => $field,
                'name' => $name,
            ];

            switch ($field) {
                case 'id':
                    $item['searchtype'] = 'contains';
                    $item['datatype'] = 'integer';
                    break;
                case 'ticket_id':
                    $item['searchtype'] = 'contains';
                    $item['datatype'] = 'specific';
                    break;
                default:
                    $item['searchtype'] = 'equals';
                    $item['datatype'] = 'specific';
                    break;
            }

            $tab[] = $item;
        }

        return $tab;
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        $pluginTicket = new PluginEstimationTicket;
        $dependencies = self::getDependencies();


        if(!is_array($values)) {
            $values = [$field => $values];
        }

        $value = $values[$field];

        switch ($field) {
            case 'author_id':
            case 'responsible_id':
                return self::findUserById($value);
            case 'ticket_id':
                $pluginTicket->setTicketId($value);
                return $pluginTicket->getTicketName();
            case 'group_id':
                foreach ($dependencies['group']->find(['id' => $value]) as $group) {
                    return $group['name'];
                }
            case 'estimation':
                return $dependencies['estimations'][$value];
            case 'is_auto':
                return $dependencies['types'][$value];
            case 'comment':
                return self::getPrintableComment($value);
        }

        return parent::getSpecificValueToDisplay($field, $values, $options); // TODO: Change the autogenerated stub
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if(!is_array($values)) {
            $values = [$field => $values];
        }

        $value = $values[$field];

        switch ($field) {
            case 'estimation':
                $options['name'] = $name;
                $options['value'] = $value;
                return parent::dropdown($options);
        }

        return parent::getSpecificValueToSelect($field, $name, $values, $options); // TODO: Change the autogenerated stub
    }


    public static function findUserById($id)
    {
        $dependencies = self::getDependencies();
        foreach ($dependencies['user']->find(['id' => $id]) as $usr) {
            return $usr['firstname'] .' '.$usr['realname'];
        }

        return false;
    }

    public static function getPrintableComment($comment)
    {
        $comm = json_decode($comment, true);

        if(!is_null($comm)) {
            return implode('<br>', $comm);
        }

        return $comment;
    }


}