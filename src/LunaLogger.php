<?php
namespace LunaDev\Utility;

use Monolog\Formatter\LineFormatter;

class LunaLogger
{
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($this->getLogFormatter());
        }
    }
    
    protected function getLogFormatter()
    {
        $action_name = request()->route()->getActionName();
        
        preg_match('/@.*[a-zA-Z0-9]/', $action_name, $matches);
        $function_name = str_replace('@', '', $matches)[0];
        preg_match('/[a-zA-Z0-9]*@/', $action_name, $matches);
        $controller_name = str_replace('@', '', $matches)[0];
        
        $request_params = json_encode(request()->all());
        $request_url = request()->fullurl();
        $transaction_id = md5(uniqid(rand(), true));
        
        config(['app.transaction_id' => $transaction_id]);
        
        $format = str_replace(
            ' %channel%.%level_name%',
            '[%level_name%]',
            LineFormatter::SIMPLE_FORMAT
        );

        $format = str_replace(
            ': %message%',
            sprintf('[%s][%s][%s] : [%s]params:request_url=[%s],request_params=[%s]%%message%%', config('app.name'), 
            $controller_name,
            $function_name,
            $transaction_id,
            $request_url,
            $request_params),
            $format
        );

        return new LineFormatter($format, null, true, true);
    }
}