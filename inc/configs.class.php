<?php


class PluginEstimationConfigs extends CommonDBTM
{
    public static function setPluginInstallDate()
    {
        $configs = new self;

        return $configs->add([
            'config_name' => 'install_date',
            'config_value' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    public function setConfig($configName, $configValue)
    {
        global $DB;

        return $DB->updateOrInsert(self::getTable(),[
            'config_name' => $configName,
            'config_value' => $configValue
        ], [
            'config_name' => $configName
        ]);
    }

    public function getConfigs($configName = null)
    {
        $result = [];
        $params = [];

        if(!is_null($configName)) {
            $params = [
                'config_name' => $configName
            ];
        }

        $configs = $this->find($params);

        foreach ($configs as  $config) {

            $name = $config['config_name'];
            $value = $config['config_value'];
            $type = $config['value_type'];

            switch ($type) {
                case 'boolean':
                    $cValue = (bool) $value;
                    break;
                case 'array':
                    $cValue = json_decode($value, true);
                    break;
                case 'string':
                    $cValue = (string) $value;
                    break;
                default:
                    $cValue = $value;
            }

            if(!is_null($configName) && $name === $configName) {
                return $cValue;
            }

            $result[$name] = $cValue;

        }

        return $result;
    }
}