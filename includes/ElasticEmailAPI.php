<?php

class ElasticEmailAPI {
    private $apiKey;
    private $baseUrl = 'https://api.elasticemail.com/v4';

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    private function request($endpoint, $method = 'GET', $data = null) {
        $ch = curl_init();
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'X-ElasticEmail-ApiKey: ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception('API Error: ' . $response);
        }

        return json_decode($response, true);
    }

    public function getCampaigns() {
        return $this->request('/campaigns');
    }

    public function getLists() {
        return $this->request('/lists');
    }

    public function getTemplates() {
        return $this->request('/templates');
    }

    public function getContacts($listId = null) {
        $endpoint = '/contacts';
        if ($listId) {
            $endpoint .= '?listId=' . $listId;
        }
        return $this->request($endpoint);
    }

    public function createCampaign($data) {
        return $this->request('/campaigns', 'POST', $data);
    }

    public function createList($data) {
        return $this->request('/lists', 'POST', $data);
    }

    public function createTemplate($data) {
        return $this->request('/templates', 'POST', $data);
    }

    public function addContact($data) {
        return $this->request('/contacts', 'POST', $data);
    }
} 