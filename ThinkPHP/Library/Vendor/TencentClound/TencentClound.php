<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9 0009
 * Time: 10:25
 */
require(__DIR__ . DIRECTORY_SEPARATOR . 'cos-autoloader.php');

class TencentClound{
    private $cosClient = '';
    private $cosRegion = '';
    private $cosAppid = '';
    private $cosKey = '';
    private $cosSecret = '';
    private $bucketName = '';
    public function __construct($bucketName = ''){
        $this->bucketName = $bucketName ? $bucketName : C('BUCKETNAME');
        $config = C('TENCENTCLOUND');
        $this->cosRegion = $config['COS_REGION'] ? $config['COS_REGION'] : '';
        $this->cosAppid = $config['COS_APPID'] ? $config['COS_APPID'] : '';
        $this->cosKey = $config['COS_KEY'] ? $config['COS_KEY'] : '';
        $this->cosSecret = $config['COS_SECRET'] ? $config['COS_SECRET'] : '';

        $cosClient = new Qcloud\Cos\Client(array('region' => $this->cosRegion,
            'credentials'=> array(
                'appId' => $this->cosAppid,
                'secretId'    => $this->cosKey,
                'secretKey' => $this->cosSecret)));
        $this->cosClient = $cosClient;
    }

    /**
     * 获取bucket列表
     * @return array
     */
    public function listBuckets(){
        try {
            $result = $this->cosClient->listBuckets();
            $res = $result->data;
            return array('code' => 0,'msg' => '获取bucket列表成功','data' => $res['Buckets']);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '获取bucket列表失败','data' => '');
        }
    }

    /**
     * 创建一个桶
     * @param string $bucketName
     * @return array
     */
    public function createBuckets($bucketName = ''){
        if(!$bucketName){
            return array('code' => -1,'msg' => '桶名称不能为空','data' => '');
        }
        try {
            $appid = $this->cosAppid;
            $result = $this->cosClient->createBucket(array('Bucket' => "{$bucketName}-{$appid}"));
            $res = $result->data;
            return array('code' => 0,'msg' => '创建桶成功','data' => $res);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '创建桶失败');
        }
    }

    /**
     * 上传文件
     * @param $bucketName 桶名称
     * @param $key 上传文件的路径
     * @param $body 文件流
     * @return array
     */
    public function uploadFile($key,$body){
        try {
            $appid = $this->cosAppid;
            $result = $this->cosClient->putObject(array(
                'Bucket' => "{$this->bucketName}-{$appid}",
                'Key' => $key,
                'Body' => $body,
                'ServerSideEncryption' => 'AES256'));
            $res = $result->data;
            return array('code' => 0,'msg' => '文件上传成功','data' => $res);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '文件上传失败');
        }
    }

    public function multipartUpload($key,$body){
        try{
            $appid = $this->cosAppid;
            $data = $this->cosClient->createMultipartUpload(
                array(
                    'Bucket' => "{$this->bucketName}-{$appid}",
                    'Key' => $key,
                    'Body' => $body,
                    'ServerSideEncryption' => 'AES256')
            ); // 初始化分块上传
            if($data['UploadId']){
                $this->cosClient->uploadPart(
                    array(
                        'Bucket' => "{$this->bucketName}-{$appid}",
                        'Key' => $data['Key'],
                        'Body' => $body,
                        'UploadId' => $data['UploadId'],
                        'ServerSideEncryption' => 'AES256')
                ); // 上传数据分块
            }

//            $this->cosClient->completeMultipartUpload(); // 完成分块上传
//            $this->cosClient->listParts(); //罗列已上传分块
//            $this->cosClient->abortMultipartUpload(); //终止分块上传

            $result = $this->cosClient->listObjects(array('Bucket' => "{$this->bucketName}-{$appid}",));
            return array('code' => 0,'msg' => '查询成功','data' => $result);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '查询失败');
        }
    }

    /**
     * 下载文件
     * @param $key 下载文件的路径名
     * @param $bucketName 桶名称
     * @param $saveAs 保存路径
     * @return array
     */
    public function downloadFile($key,$saveAs){
        try{
            $appid = $this->cosAppid;
            $result = $this->cosClient->getObject(array(
                //bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
                'Bucket' => "{$this->bucketName}-{$appid}",
                'Key' => $key,
                'SaveAs' => $saveAs));
            $res = $result->data;
            return array('code' => 0,'msg' => '文下载成功','data' => $res);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '文件下载失败');
        }
    }

    /**
     * 删除一个文件
     * @param $key
     * @param $bucketName
     * @return array
     */
    public function deleteFile($key){
        try{
            $appid = $this->cosAppid;
            $result = $this->cosClient->deleteObject(array(
                //bucket的命名规则为{name}-{appid} ，此处填写的存储桶名称必须为此格式
                'Bucket' => "{$this->bucketName}-{$appid}",
                'Key' => $key));
            $res = $result->data;
            return array('code' => 0,'msg' => '文删除成功','data' => $res);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '文件删除失败');
        }
    }

    /**
     * 查询bucket是否存在
     * @param $bucketName
     * @return array
     */
    public function headBucket($bucketName){
        try{
            $appid = $this->cosAppid;
            $result = $this->cosClient->headBucket(array('Bucket' => "$bucketName-$appid"));
            $res = $result->data;
            return array('code' => 0,'msg' => '查询成功','data' => $res);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '查询失败');
        }
    }

    /**
     * 获取下载链接
     * @param $fname
     * @return array
     */
    public function getDownLoadUrl($fname){
        try{
            $key = substr($fname,strpos($fname,'myqcloud.com') + 13);
            $url = "/{$key}";
            $appid = $this->cosAppid;
            $request = $this->cosClient->get($url);
            $signedUrl = $this->cosClient->getObjectUrl("$this->bucketName-$appid", $key, '+10 minutes');
            return array('code' => 0,'msg' => '查询成功','data' => $signedUrl);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '查询失败');
        }
    }

    /**
     * COS 文件列表
     * @return array
     */
    public function objList(){
        try{
            $appid = $this->cosAppid;
            $result = $this->cosClient->listObjects(array('Bucket' => "{$this->bucketName}-{$appid}",));
            return array('code' => 0,'msg' => '查询成功','data' => $result);
        } catch (\Exception $e) {
            return array('code' => -1,'msg' => '查询失败');
        }
    }


    public function log($action,$result){
        \Think\Log::record("action:{$action}----result:" . print_r($result,1));
    }
}