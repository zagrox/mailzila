<?php

class ElasticEmailAPI {
    private $apiKey;
    private $baseUrl = 'https://api.elasticemail.com/v4';

    public function __construct($apiKey) {
        if (empty($apiKey)) {
            throw new Exception('API key is required');
        }
        $this->apiKey = $apiKey;
    }

    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $ch = curl_init($this->baseUrl . $endpoint);
        
        $headers = [
            'X-ElasticEmail-ApiKey: ' . $this->apiKey,
            'Content-Type: application/json'
        ];

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
            throw new Exception('API request failed with status code ' . $httpCode);
        }

        return json_decode($response, true);
    }

    public function getCampaigns() {
        return $this->makeRequest('/campaigns');
    }

    public function getCampaign($campaignId) {
        return $this->makeRequest('/campaigns/' . $campaignId);
    }

    public function getCampaignStats($campaignId) {
        return $this->makeRequest('/campaigns/' . $campaignId . '/stats');
    }

    public function createCampaign($data) {
        return $this->makeRequest('/campaigns', 'POST', $data);
    }

    public function updateCampaign($campaignId, $data) {
        return $this->makeRequest('/campaigns/' . $campaignId, 'PUT', $data);
    }

    public function deleteCampaign($campaignId) {
        return $this->makeRequest('/campaigns/' . $campaignId, 'DELETE');
    }

    public function getLists() {
        return $this->makeRequest('/lists');
    }

    public function getList($listId) {
        return $this->makeRequest('/lists/' . $listId);
    }

    public function createList($data) {
        return $this->makeRequest('/lists', 'POST', $data);
    }

    public function updateList($listId, $data) {
        return $this->makeRequest('/lists/' . $listId, 'PUT', $data);
    }

    public function deleteList($listId) {
        return $this->makeRequest('/lists/' . $listId, 'DELETE');
    }

    public function getContacts($listId = null) {
        $endpoint = $listId ? '/lists/' . $listId . '/contacts' : '/contacts';
        return $this->makeRequest($endpoint);
    }

    public function getContact($contactId) {
        return $this->makeRequest('/contacts/' . $contactId);
    }

    public function addContact($data) {
        return $this->makeRequest('/contacts', 'POST', $data);
    }

    public function updateContact($contactId, $data) {
        return $this->makeRequest('/contacts/' . $contactId, 'PUT', $data);
    }

    public function deleteContact($contactId) {
        return $this->makeRequest('/contacts/' . $contactId, 'DELETE');
    }

    public function bulkDeleteContacts($contactIds) {
        return $this->makeRequest('/contacts/bulk/delete', 'POST', ['contactIds' => $contactIds]);
    }

    public function bulkMoveContacts($contactIds, $targetListId) {
        return $this->makeRequest('/contacts/bulk/move', 'POST', [
            'contactIds' => $contactIds,
            'targetListId' => $targetListId
        ]);
    }

    public function getContactActivity($contactId) {
        return $this->makeRequest('/contacts/' . $contactId . '/activity');
    }

    public function getContactScore($contactId) {
        return $this->makeRequest('/contacts/' . $contactId . '/score');
    }

    public function getContactSegments($listId = null) {
        $endpoint = $listId ? '/lists/' . $listId . '/segments' : '/segments';
        return $this->makeRequest($endpoint);
    }

    public function createSegment($data) {
        return $this->makeRequest('/segments', 'POST', $data);
    }

    public function updateSegment($segmentId, $data) {
        return $this->makeRequest('/segments/' . $segmentId, 'PUT', $data);
    }

    public function deleteSegment($segmentId) {
        return $this->makeRequest('/segments/' . $segmentId, 'DELETE');
    }

    public function getTemplates() {
        return $this->makeRequest('/templates?scopeType=Global');
    }

    public function getTemplate($templateId) {
        return $this->makeRequest('/templates/' . $templateId);
    }

    public function createTemplate($data) {
        return $this->makeRequest('/templates', 'POST', $data);
    }

    public function updateTemplate($templateId, $data) {
        return $this->makeRequest('/templates/' . $templateId, 'PUT', $data);
    }

    public function deleteTemplate($templateId) {
        return $this->makeRequest('/templates/' . $templateId, 'DELETE');
    }
} 