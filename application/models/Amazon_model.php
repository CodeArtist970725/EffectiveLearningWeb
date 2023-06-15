<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/../vendor/autoload.php';

use Aws\S3\S3Client;

class AwsBucket
{
    private $bucket, $accessKeyId, $secretAccessKey, $region;
    private $s3Client;

    public function __construct($bucket_name = null)
    {
        $this->bucket = $bucket_name;
        $this->accessKeyId = "AKIAUYULH3FUUVRLFTOU";
        $this->secretAccessKey = "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";
        $this->region = "us-east-2";

        $this->s3Client = S3Client::factory([
            'credentials' => new Aws\Credentials\Credentials($this->accessKeyId, $this->secretAccessKey),
            'region' => $this->region,
            'version' => 'latest'
        ]);
    }

    public function jsAccessConfig()
    {
        return [
            'a' => [
                'accessKeyId' => $this->accessKeyId,
                'secretAccessKey' => $this->secretAccessKey
            ],
            'b' => $this->region,
            'c' => [
                'params' => [
                    'Bucket' => $this->bucket
                ]
            ]
        ];
    }

    public function sendFile($full_path, $filename)
    {
        $result = null;
        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $filename,
                'Body' => fopen($full_path, "r"),
                'ACL' => 'public-read'
            ]);
        } catch (Exception $e) {
            $result = $e;
        }
        unlink($full_path);
        return $result['ObjectURL'];
    }
}

class Amazon_model extends CI_Model
{
    public $stuAdminBucket, $insAdminBucket, $courseVideoBucket, $lessonVideoBucket, $lessonAttBucket;
    private $tmp_directory;

    public function __construct()
    {
        parent::__construct();

        $this->load->library('upload');

        $this->stuAdminBucket = new AwsBucket('phyostudentadminstore');
        $this->insAdminBucket = new AwsBucket('phyoinstructoradminstore');
        $this->courseVideoBucket = new AwsBucket('phyocoursevideostore');
        $this->lessonVideoBucket = new AwsBucket('phyolessonvideostore');
        $this->lessonAttBucket = new AwsBucket('phyolessonattachmentstore');

        $this->tmp_directory = APPPATH.'../uploads/tmp_files';
        if(!is_dir($this->tmp_directory)) mkdir($this->tmp_directory);
    }

    public function uploadStudentAdmin($form_file_name = 'file')
    {
        $this->upload->initialize([
            'upload_path' => $this->tmp_directory,
            'allowed_types' => 'gif|jpg|png'
        ]);
        if(!$this->upload->do_upload($form_file_name)) {
            return ['error' => $this->upload->display_errors()];
        }
        $full_path = $this->upload->data()['full_path'];
        $file_name = date("Y-m-d", time())."-".$this->upload->data()['file_name'];

        return $this->stuAdminBucket->sendFile($full_path, $file_name);
    }

    public function uploadInstructorAdmin($form_file_name = 'file')
    {
        $this->upload->initialize([
            'upload_path' => $this->tmp_directory,
            'allowed_types' => 'gif|jpg|png'
        ]);
        if(!$this->upload->do_upload($form_file_name)) {
            return ['error' => $this->upload->display_errors()];
        }
        $full_path = $this->upload->data()['full_path'];
        $file_name = date("Y-m-d", time())."-".$this->upload->data()['file_name'];
        return $this->insAdminBucket->sendFile($full_path, $file_name);
    }

    public function uploadLessonAttachment($form_file_name = 'file')
    {
        $this->upload->initialize([
            'upload_path' => $this->tmp_directory,
            'allowed_types' => 'gif|jpg|png|doc|docx|pdf|txt'
        ]);
        if(!$this->upload->do_upload($form_file_name)) {
            return ['error' => $this->upload->display_errors()];
        }
        $full_path = $this->upload->data()['full_path'];
        $file_name = date("Y-m-d", time())."-".$this->upload->data()['file_name'];
        return $this->lessonAttBucket->sendFile($full_path, $file_name);
    }

    public function getJsLessonConfig()
    {
        $result = $this->lessonVideoBucket->jsAccessConfig();
        $result['prefix'] = date("Y-m-d", time())."-";
        return $result;
    }

    public function getJsCourseConfig()
    {
        $result = $this->courseVideoBucket->jsAccessConfig();
        $result['prefix'] = date('Y-m-d', time())."-";
        return $result;
    }
}