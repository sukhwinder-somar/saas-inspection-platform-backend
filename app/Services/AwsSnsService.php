<?php

namespace App\Services;

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class AwsSnsService
{
    private $snsClient;
    private $defaultTopicArn;

    public function __construct()
    {
        $this->snsClient = new SnsClient([
            'version' => 'latest',
            'region' => config('services.aws.region', 'us-east-1'),
            'credentials' => [
                'key' => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
        ]);

        $this->defaultTopicArn = config('services.aws.sns.default_topic_arn');
    }

    /**
     * Send a notification message via SNS
     */
    public function sendNotification(string $message, string $subject = null, string $topicArn = null): bool
    {
        try {
            $params = [
                'TopicArn' => $topicArn ?? $this->defaultTopicArn,
                'Message' => $message,
            ];

            if ($subject) {
                $params['Subject'] = $subject;
            }

            $result = $this->snsClient->publish($params);

            Log::info('SNS notification sent successfully', [
                'message_id' => $result['MessageId'],
                'topic_arn' => $params['TopicArn'],
                'subject' => $subject,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('Failed to send SNS notification', [
                'error' => $e->getMessage(),
                'topic_arn' => $topicArn ?? $this->defaultTopicArn,
                'subject' => $subject,
            ]);

            return false;
        }
    }

    /**
     * Send SMS via SNS
     */
    public function sendSms(string $phoneNumber, string $message): bool
    {
        try {
            $result = $this->snsClient->publish([
                'PhoneNumber' => $phoneNumber,
                'Message' => $message,
            ]);

            Log::info('SMS sent successfully via SNS', [
                'message_id' => $result['MessageId'],
                'phone_number' => $phoneNumber,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('Failed to send SMS via SNS', [
                'error' => $e->getMessage(),
                'phone_number' => $phoneNumber,
            ]);

            return false;
        }
    }

    /**
     * Create a new SNS topic
     */
    public function createTopic(string $topicName): ?string
    {
        try {
            $result = $this->snsClient->createTopic([
                'Name' => $topicName,
            ]);

            $topicArn = $result['TopicArn'];

            Log::info('SNS topic created successfully', [
                'topic_name' => $topicName,
                'topic_arn' => $topicArn,
            ]);

            return $topicArn;
        } catch (AwsException $e) {
            Log::error('Failed to create SNS topic', [
                'error' => $e->getMessage(),
                'topic_name' => $topicName,
            ]);

            return null;
        }
    }

    /**
     * Subscribe an email to a topic
     */
    public function subscribeEmail(string $email, string $topicArn = null): bool
    {
        try {
            $this->snsClient->subscribe([
                'TopicArn' => $topicArn ?? $this->defaultTopicArn,
                'Protocol' => 'email',
                'Endpoint' => $email,
            ]);

            Log::info('Email subscribed to SNS topic', [
                'email' => $email,
                'topic_arn' => $topicArn ?? $this->defaultTopicArn,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('Failed to subscribe email to SNS topic', [
                'error' => $e->getMessage(),
                'email' => $email,
                'topic_arn' => $topicArn ?? $this->defaultTopicArn,
            ]);

            return false;
        }
    }

    /**
     * Subscribe a phone number to a topic
     */
    public function subscribePhoneNumber(string $phoneNumber, string $topicArn = null): bool
    {
        try {
            $this->snsClient->subscribe([
                'TopicArn' => $topicArn ?? $this->defaultTopicArn,
                'Protocol' => 'sms',
                'Endpoint' => $phoneNumber,
            ]);

            Log::info('Phone number subscribed to SNS topic', [
                'phone_number' => $phoneNumber,
                'topic_arn' => $topicArn ?? $this->defaultTopicArn,
            ]);

            return true;
        } catch (AwsException $e) {
            Log::error('Failed to subscribe phone number to SNS topic', [
                'error' => $e->getMessage(),
                'phone_number' => $phoneNumber,
                'topic_arn' => $topicArn ?? $this->defaultTopicArn,
            ]);

            return false;
        }
    }

    /**
     * List all topics
     */
    public function listTopics(): array
    {
        try {
            $result = $this->snsClient->listTopics();
            return $result['Topics'] ?? [];
        } catch (AwsException $e) {
            Log::error('Failed to list SNS topics', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
