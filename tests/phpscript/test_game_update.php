<?php
class Test_game_update extends CodeIgniterUnitTestCase
{
    public function __construct()
    {
        parent::__construct('test_game_update');
        require_once('daemon/game_update.php');
        require_once('daemon/cpkadjust.php');
    }
    
    public function setUp() {
        
    }

    public function tearDown() {
        
    }

    public function test_unzip(){
        $file_path = 'tests/testfile/uploadzip.zip';
        $dir_path = 'tests/testfile/uploadzip';
        $result = unzip($file_path,$dir_path,TRUE); //删除源文件
        $this->assertTrue($result);
        $this->assertTrue(is_dir($dir_path));
        $this->assertTrue(!is_file($file_path));
        $this->assertTrue(file_exists($dir_path.'/assets.md5'));
    }
    
    public function test_createzip() {
        $file_path = 'tests/testfile/uploadzip.zip';
        $dir_path = 'tests/testfile/uploadzip';
        $result = createzip($dir_path, FALSE, TRUE);
        $this->assertTrue($result);
        $this->assertTrue(!is_dir($dir_path));
        $this->assertTrue(is_file($file_path));
    }
    
    public function test_merge_zip() {
        $old_zip = 'tests/testfile/old.zip';
        $new_zip = 'tests/testfile/new.zip';
        $target = 'tests/testfile/merge.zip';
        $result = merge_zip($old_zip,$new_zip,$target);
        $this->assertTrue($result);
        $zip = new PclZip($target);
        $files = $zip->listContent();
        $content = array(
            'a.txt' => FALSE,
            'b.txt' => FALSE,
            'c.txt' => FALSE,
        );
        foreach ($files as $value) {
            if(key_exists($value['filename'], $content))
            {
                $content[$value['filename']] = TRUE;
                if($value['filename'] === 'a.txt')
                {
                    $index = $value['index'];
                }
            }
        }
        foreach ($content as $value) {
            $this->assertTrue($value);
        }
        if(isset($index))
        {
            $a = $zip->extract(PCLZIP_OPT_BY_INDEX,$index,PCLZIP_OPT_EXTRACT_AS_STRING);
            $this->assertTrue($a[0]['content'] == 1);   //新文件内容为1，旧文件为0
        }
        else
        {
            //相同文件a.txt不存在报错
            $this->assertTrue(FALSE);
        }
        unlink($target);
    }
    
    public function test_operation() {
        $hotversioncode = 11111;
        $manifest_path = FCPATH.'tests/testfile/origin_scene/SceneManifest.xml';
        $cpk_resource_dir = FCPATH.'tests/testfile/origin_scene';
        $new_cpk_resource_dir = FCPATH.'tests/testfile/new_scene';
        $hotversion_bak =  FCPATH.'tests/testfile/new_scene/hot_resources_bak';
        $hot_version_dir = FCPATH.'tests/testfile/new_scene/hot_resources';
        $this->resource_copy($hotversion_bak, $hot_version_dir);
        $something_to_add = array(
            'a_to_add.txt',
            'b_to_add.txt',
            'add_dir/c_to_add.txt',
            'add_dir/d_to_add.txt',
        );
        $something_to_delete = array(
            'resource/delete/delete_a.txt',
            'resource/delete/delete_b.txt',
        );
        $something_to_move = array(
            'resource/move/move_a.txt' => 'scene_c002.cpk',
            'resource/move/move_b.txt' => 'scene_c002.cpk',
        );
        $something_to_add = $this->random_file($something_to_add);
        //用于替换的文件
        array_push($something_to_add, 'resource/add/dirtoadd.txt');
        $something_to_delete = $this->random_file($something_to_delete);
        $something_to_move = $this->random_file($something_to_move);
        $myadjust = array(
            array(
                'add' => $something_to_add,
                'delete' => $something_to_delete,
                'move' => $something_to_move,
                'scene_name' => 'scene_c001.cpk',
            ),
        );
        $myadjust_json = json_encode($myadjust);
        $myplan = FCPATH.'tests/testfile/new_scene/SceneAdjust.json';
        file_put_contents($myplan, $myadjust_json);
        
        $cpk = new CpkResource($hotversioncode, $manifest_path, $cpk_resource_dir, $new_cpk_resource_dir, $hot_version_dir, $myplan);
        echo '检查差分文件是否生成';
        $this->assertTrue($cpk->create_chafen());
        $chafen_path = $new_cpk_resource_dir."/chafen_$hotversioncode.cpk";
        $this->assertTrue(is_file($chafen_path));
        unlink($chafen_path);
        
        echo '检查manifest.zip是否生成';
        $this->assertTrue($cpk->create_manifestzip());
        $manifestzip_path = $new_cpk_resource_dir.'/SceneManifest.zip';
        $this->assertTrue(is_file($manifestzip_path));
        unlink($manifestzip_path);

        echo '检查adjust';
        try {
            $cpk->adjust();
            $this->assertTrue(TRUE);
        } catch (Exception $exc) {
            echo $exc->getMessage();
            $this->assertTrue(FALSE);
        }
        
        $origin = glob($cpk_resource_dir.'/*.cpk');
        foreach ($origin as $key => $value) {
            $tmp = new PclZip($value);
            $filename = basename($value);
            $origin_cpks[$filename] = $tmp->listContent();
            $new_cpks[$filename] = str_replace('origin_scene','new_scene', $value);
        }
        $newcpk_exist = FALSE;
        foreach ($new_cpks as $key => $value) {
            if(is_file($value))
            {
                echo '生成新cpk'.$key;
                $tmp = new PclZip($value);
                $new_cpks[$key] = $tmp->listContent();
                $this->assertTrue(TRUE);
                unlink($value);
                $newcpk_exist = TRUE;
            }
            else
            {
                //新cpk没生成
                echo '新cpk '.$key.' 未生成';
                $this->assertTrue(FALSE);
            }
        }
        if($newcpk_exist)
        {
            echo '验证文件内容'."\n";
            foreach ($myadjust as $value) {
                $scene_name = $value['scene_name'];
                foreach ($value['add'] as $addfile) {
                    if(strcmp($addfile, 'resource/add/dirtoadd.txt') === 0)
                    {
                        $this->assertTrue($this->inzip($addfile, $origin_cpks[$scene_name]));
                        $this->assertTrue($this->inzip($addfile, $new_cpks[$scene_name]));
                        $filenum = 0;
                        foreach ($new_cpks[$scene_name] as $value) {
                            if(strcmp($value['filename'], 'resource/add/dirtoadd.txt') === 0)
                            {
                                $filenum++;
                            }
                        }
                        //检查add操作没有替换掉原来文件的bug
                        echo $filenum;
                        $this->assertTrue($filenum === 1);
                    }
                    else
                    {
                        $this->assertFalse($this->inzip($addfile, $origin_cpks[$scene_name]));
                        $this->assertTrue($this->inzip($addfile, $new_cpks[$scene_name]));
                    }
                }
                foreach ($value['delete'] as $deletefile) {
                    $this->assertTrue($this->inzip($deletefile, $origin_cpks[$scene_name]));
                    $this->assertFalse($this->inzip($deletefile, $new_cpks[$scene_name]));
                }
                foreach ($value['move'] as $movefile => $moveto) {
                    $this->assertTrue($this->inzip($movefile, $origin_cpks[$scene_name]));
                    $this->assertFalse($this->inzip($movefile, $new_cpks[$scene_name]));
                    $this->assertTrue($this->inzip($movefile, $new_cpks[$moveto]));
                }
            }
        }
        $this->del_dir($hot_version_dir);
    }
    
    
    protected function resource_copy($src, $dst, $rename = FALSE) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $this->resource_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    if ($rename) {
                        rename($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        copy($src . '/' . $file, $dst . '/' . $file);
                    }
                }
            }
        }
        closedir($dir);
        return $dst;
    }

    protected function del_dir($dir) {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->del_dir($fullpath);
                }
            }
        }
        closedir($dh);
        if (rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    }

    protected function gen_test_res() {
        //1先生成随机的文件结构
        //2生成manifest
        //3生成hot_res
        //4生成adjust
        //5生成新的manifest
        $workdir = FCPATH.'tests/testfile/game_update2';
        $hotres = array(
            'map/addtocpk1.txt',
        );
        $cpk1 = array(
            'res/delete.txt',
            'action/move.txt',
        );
    }
    
    protected function check_files() {
        
    }
    
    protected function inzip($file,$filelist) {
        foreach ($filelist as $value) {
            if(strcmp($value['filename'], $file) === 0)
            {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    private function random_file($files) {
        $file_num = count($files);
        $arr=range(0,$file_num-1);
        shuffle($arr);
        $n = mt_rand(0, $file_num);
        for($i=0; $i<$n; $i++)
        {
            unset($files[$arr[$i]]);
        }
        return $files;
    }
}
