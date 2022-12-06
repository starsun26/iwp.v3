<?php

class TextorobotApi
{    
    private $apiKey;
    private $curlConfig;
    private $errorHandler;
    private $baseUrl = 'https://textorobot.ru/api/';

    public function __construct($apiKey, $errorHandler = null)
    {
        $this->apiKey = $apiKey;
        $this->curlConfig = array(
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
        );
        $this->errorHandler = $errorHandler;
    }
    
    public function synonymizeText($text)
    {
        $resultData = $this->jsonQuery('synonymize', [ 'text' => $text ]);
        
        if ($resultData !== false)
        {
            return $resultData->processedText;
        }
        
        return false;
    }
    
    public function synonymize($text)
    {
        return $this->jsonQuery('synonymize', [ 'text' => $text ]);
    }
    
    public function balance()
    {
        return $this->jsonQuery('balance', []);
    }
    
    private function jsonQuery($request, $params)
    {
        $params['apiKey'] = $this->apiKey;

        $httpQuery = http_build_query($params, '', '&');

        $ch = curl_init();
        curl_setopt_array($ch, $this->curlConfig);
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl.$request);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $httpQuery);
        
        $resultData = false;

        $json = curl_exec($ch);
        $errno = curl_errno($ch);

        // если произошла ошибка
        if (!$errno) {
            $result = json_decode($json);
            
            if ($result->success) {
                $resultData = $result->data;
                if (isset($result->messages->warning)) {
                    foreach ($result->messages->warning as $warning) {
                        $this->addWarning($warning);
                    }
                }
            } else if (isset($result->messages->error)) {
                foreach ($result->messages->error as $error) {
                    $this->addError($error);
                }
            } else {
                $this->addError("Неизвестная ошибка");
            }
        } else {
            $errorMessage = curl_error($ch);
            $this->addError('Ошибка HTTP запроса: ' . $errorMessage);
        }

        curl_close($ch);

        return $resultData;
    }

    private function addError($errorText)
    {
        if (isset($this->errorHandler)) {
            call_user_func($this->errorHandler, $errorText, 'error');
        }
    }

    private function addWarning($warningText)
    {
        if (isset($this->errorHandler)) {
            call_user_func($this->errorHandler, $warningText, 'warning');
        }
    }
}

?>
