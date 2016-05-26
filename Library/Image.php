<?php
define("JPG", 0);
define("GIF", 1);
define("PNG", 2);
define("BMP", 3);
define("JPG_QUALITY", 100);
define("PNG_QUALITY", 0);

/**
 * Lớp mở rộng hỗ trợ việc xử lý ảnh
 * @author		Mr.UBKey
 * @link		http://xphp.xweb.vn/user_guide/xphp_image.html
 */
class Image
{
	/**
	 * Kiểu resize với chiều rộng và chiều cao chính xác
     *
	 * @var string
	 */
	const EXACT = 'exact';

	/**
	 * Kiểu resize với chiều rộng và chiều cao theo chân dung
     *
	 * @var string
	 */
	const PORTRAIT = 'portrait';

	/**
	 * Kiểu resize với chiều rộng và chiều cao theo cảnh quan
     *
	 * @var string
	 */
	const LANDSCAPE = 'landscape';

	/**
	 * Kiểu resize với chiều rộng và chiều cao được căn tự động
     *
	 * @var string
	 */
	const AUTO = 'auto';

	/**
	 * Kiểu resize với chiều rộng và chiều cao bị cắt cho phù hợp
     *
	 * @var string
	 */
	const CROP = 'crop';

    /**
     * Kiểu resize với chiều rộng và chiều cao bị cắt từ top cho phù hợp
     *
     * @var string
     */
    const CROP_TOP = 'crop_top';
	
	
    protected $filename;

    protected $image;

    protected $width;

    protected $height;

    protected $data;

    protected $copy;

    protected $type;

    /**
     * @param $filename Tên file ảnh
     *
     * @throws \Exception
     */
    public function __construct($filename)
    {
        if (! is_file($filename))
            throw new Exception("File does not exist");
        $this->filename = $filename;
        $this->data = getimagesize($this->filename);
        switch ($this->data['mime']) {
            case 'image/pjpeg':
                $this->type = 'PJPG';
                break;
            case 'image/jpeg':
                $this->type = 'JPG';
                $this->image = imagecreatefromjpeg($this->filename);
                break;
            case 'image/gif':
                $this->type = 'GIF';
                $this->image = imagecreatefromgif($this->filename);
                break;
            case 'image/png':
                $this->type = 'PNG';
                $this->image = imagecreatefrompng($this->filename);
                break;
            case 'image/x-ms-bmp':
                $this->type = 'BMP';
                $this->image = imagecreatefromwbmp($this->filename);
                //$this->image = $this->imagecreatefrombmp($this->filename);
                break;
            default:
                throw new Exception("File format is not supported");
                break;
        }
        // *** Lấy ra độ rộng và độ cao gốc
        $this->width  = imagesx($this->image);
        $this->height = imagesy($this->image);
    }
    
    public function getInfo()
    {
    	return $this->data;
    }
    
    /**
     * Tạo một bản copy từ bản gốc của ảnh
     */
    public function duplicate ()
    {
        if (! isset($this->image))
            throw new Exception("No image loaded");
        $this->copy = $this->image;
    }
    
    /**
     * Resize ảnh với nhiều lựa chọn
     *
     * @param int $newWidth Chiều rộng
     * @param int $newHeight Chiều cao
     * @param string $option Chọn kiểu resize
     */
    public function resizeImage($newWidth, $newHeight, $crop="auto", $overlay = false)
    {
        if(empty($newHeight))
            $newHeight = $this->getSizeByFixedWidth($newWidth);
    	// *** Get optimal width and height - based on $option
    	$optionArray = $this->getDimensions($newWidth, $newHeight, strtolower($crop));
    
    	$optimalWidth  = $optionArray['optimalWidth'];
    	$optimalHeight = $optionArray['optimalHeight'];
    
    	// *** Resample - create image canvas of x, y size
    	$this->copy = imagecreatetruecolor($optimalWidth, $optimalHeight);
    	imagecopyresampled($this->copy, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

        switch($crop)
        {
            case 'crop':
                $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
                break;
            case 'crop_top':
                $this->crop($newWidth, $newHeight, $newWidth, $newHeight);
                break;
            case 'crop_tm': //Crop top-middle
                $this->crop($optimalWidth, $newHeight, $newWidth, $newHeight);
                break;
        }
        if($overlay)
            $this->overlay($overlay);
    }
    
    private function getDimensions($newWidth, $newHeight, $option)
    {
    
    	switch ($option)
    	{
    		case 'exact':
    			$optimalWidth = $newWidth;
    			$optimalHeight= $newHeight;
    			break;
    		case 'portrait':
    			$optimalWidth = $this->getSizeByFixedHeight($newHeight);
    			$optimalHeight= $newHeight;
    			break;
    		case 'landscape':
    			$optimalWidth = $newWidth;
    			$optimalHeight= $this->getSizeByFixedWidth($newWidth);
    			break;
    		case 'auto':
    			$optionArray = $this->getSizeByAuto($newWidth, $newHeight);
    			$optimalWidth = $optionArray['optimalWidth'];
    			$optimalHeight = $optionArray['optimalHeight'];
    			break;
    		case 'crop':
    			$optionArray = $this->getOptimalCrop($newWidth, $newHeight);
    			$optimalWidth = $optionArray['optimalWidth'];
    			$optimalHeight = $optionArray['optimalHeight'];
    			break;
            case 'crop_top':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop_tm':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
    	}
    	return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    private function getSizeByFixedHeight($newHeight)
    {
    	$ratio = $this->width / $this->height;
    	$newWidth = $newHeight * $ratio;
    	return $newWidth;
    }
    
    private function getSizeByFixedWidth($newWidth)
    {
    	$ratio = $this->height / $this->width;
    	$newHeight = $newWidth * $ratio;
    	return $newHeight;
    }
    
    private function getSizeByAuto($newWidth, $newHeight)
    {
    	if ($this->height < $this->width)
    		// *** Image to be resized is wider (landscape)
    	{
    		$optimalWidth = $newWidth;
    		$optimalHeight= $this->getSizeByFixedWidth($newWidth);
    	}
    	elseif ($this->height > $this->width)
    	// *** Image to be resized is taller (portrait)
    	{
    		$optimalWidth = $this->getSizeByFixedHeight($newHeight);
    		$optimalHeight= $newHeight;
    	}
    	else
    		// *** Image to be resizerd is a square
    	{
    		if ($newHeight < $newWidth) {
    			$optimalWidth = $newWidth;
    			$optimalHeight= $this->getSizeByFixedWidth($newWidth);
    		} else if ($newHeight > $newWidth) {
    			$optimalWidth = $this->getSizeByFixedHeight($newHeight);
    			$optimalHeight= $newHeight;
    		} else {
    			// *** Sqaure being resized to a square
    			$optimalWidth = $newWidth;
    			$optimalHeight= $newHeight;
    		}
    	}
    
    	return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    private function getOptimalCrop($newWidth, $newHeight)
    {
    
    	$heightRatio = $this->height / $newHeight;
    	$widthRatio  = $this->width /  $newWidth;
    
    	if ($heightRatio < $widthRatio) {
    		$optimalRatio = $heightRatio;
    	} else {
    		$optimalRatio = $widthRatio;
    	}
    
    	$optimalHeight = $this->height / $optimalRatio;
    	$optimalWidth  = $this->width  / $optimalRatio;
    
    	return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
	private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)  
	{  
	    // *** Find center - this will be used for the crop  
	    $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );  
	    $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );  
	  
	    $crop = $this->copy;  
	    //imagedestroy($this->imageResized);  
	  
	    // *** Now crop from center to exact requested size  
	    $this->copy = imagecreatetruecolor($newWidth , $newHeight);  
	    imagecopyresampled($this->copy, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);  
	}

    private function overlay($ovlSrc, $dst_x = 0, $dst_y = 0, $src_x = 0, $src_y = 0, $src_w = 0, $src_h = 0, $pct = 0)
    {
        if(empty($src_w))
            $src_w = $this->width;
        if(empty($src_h))
            $src_h = $this->height;
        if(empty($pct))
            $pct = 60;
        imagecopymerge($this->filename, $ovlSrc, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct);
    }
	
	/**
	 * Cắt ảnh
     *
	 * @param int $x Vị trí cắt X
	 * @param int $y Vị trí cắt Y
	 * @param int $width Chiều rộng ảnh
	 * @param int $height Chiều cao ảnh
	 * @param int $newWidth Điểm cắt tới của chiều rộng
	 * @param int $newHeight Điểm cắt tới của chiều cao
	 */
	public function cropImage($x, $y, $width, $height, $newWidth, $newHeight)
	{
		$this->copy = imagecreatetruecolor($width , $height);
		imagecopyresampled($this->copy, $this->image , 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
	}
  
    /**
     * Resize ảnh theo số tối đa hoặc tối thiểu của chiều rộng và cao
     *
     * @param int $wx Chiều rộng tối đa
     * @param int $hx Chiều cao tối đa
     * @param int $wm Chiều rộng tối thiểu
     * @param int $hm Chiều cao tối thiểu
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function resize ($wx, $hx, $wm = 0, $hm = 0)
    {
        if (! isset($this->image))
            throw new \Exception("No image loaded");
        if ($wx != $wm && $hx != $hm && $wm != 0 && $hm != 0)
            throw new \Exception("Bad dimensions specified");
        $r = $this->data[0] / $this->data[1];
        $rx = $wx / $hx;
        if ($wm == 0 || $hm == 0)
            $rm = $rx;
        else
            $rm = $wm / $hm;
        $dx = 0;
        $dy = 0;
        $sx = 0;
        $sy = 0;
        $dw = 0;
        $dh = 0;
        $sw = 0;
        $sh = 0;
        $w = 0;
        $h = 0;
        if ($r > $rx && $r > $rm) {
            $w = $wx;
            $h = $hx;
            $sw = $this->data[1] * $rx;
            $sh = $this->data[1];
            $sx = ($this->data[0] - $sw) / 2;
            $dw = $wx;
            $dh = $hx;
        } elseif ($r < $rm && $r < $rx) {
            $w = $wx;
            $h = $hx;
            $sh = $this->data[0] / $rx;
            $sy = ($this->data[1] - $sh) / 2;
            $sw = $this->data[0];
            $dw = $wx;
            $dh = $hx;
        } elseif ($r >= $rx && $r <= $rm) {
            $w = $wx;
            $h = $wx / $r;
            $dw = $wx;
            $dh = $wx / $r;
            $sw = $this->data[0];
            $sh = $this->data[1];
        } elseif ($r <= $rx && $r >= $rm) {
            $w = $hx * $r;
            $h = $hx;
            $dw = $hx * $r;
            $dh = $hx;
            $sw = $this->data[0];
            $sh = $this->data[1];
        } else {
            throw new \Exception("Can't resize the image");
        }
        $this->copy = imagecreatetruecolor($w, $h);
        imagecopyresampled($this->copy, $this->image, $dx, $dy, $sx, $sy, $dw, 
        $dh, $sw, $sh);
        return true;
    }

    /**
     * Lưu bản sao của ảnh. Nếu không truyền vào tên file hệ thống sẽ tự ghi đè lên bản gốc
     *
     * @param string $filename Tên file ảnh
     * @param define $type Kiểu ảnh JPG, GIF, PNG, BMP
     * @param bool $fileExtInc Hệ thống tự động add thêm đuôi tệp tin ảnh hay không
     *
     * @return string
     *
     * @throws \Exception
     */
    public function save ($filename = NULL, $type = NULL, $fileExtInc = true)
    {
        //Lấy ra kiểu file mặc định lấy từ tập tin gốc
        if($type === NULL)
            $type = $this->type;
        
        if (! isset($this->copy))
            $this->copy = $this->image;
        if ($filename === NULL)
        {
            $fileExtInc = false;
            $filename = $this->filename;
        }
        switch ($type) {
            case GIF:
                if($fileExtInc)
                    $filename .= '.gif';
                imagegif($this->copy, $filename);
                return $filename;
                break;
            case PNG:
                if($fileExtInc)
                    $filename .= '.png';
                imagepng($this->copy, $filename, PNG_QUALITY);
                return $filename;
                break;
            case BMP:
                if($fileExtInc)
                    $filename .= '.bmp';
                imagewbmp($this->copy, $filename);
                return $filename;
                break;
            case JPG:
            default:
                if($fileExtInc)
                    $filename .= '.jpeg';
                imagejpeg($this->copy, $filename, JPG_QUALITY);
                return $filename;
                break;
        }
        throw new Exception("Save failed");
    }

    /**
     * Chuyển đổi bản sao thành chuỗi và trả về chuỗi
     *
     * @param string $type
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getString ($type = NULL)
    {
        if (! isset($this->copy))
            throw new \Exception("No copy to return");
        $contents = ob_get_contents();
        if ($contents !== false)
            ob_clean();
        else
            ob_start();
        $this->show($type);
        $data = ob_get_contents();
        if ($contents !== false) {
            ob_clean();
            echo $contents;
        } else
            ob_end_clean();
        return $data;
    }

    /**
     * Hiển thị chuỗi được tạo ra bởi getString()
     *
     * @param $type Kiểu của ảnh JPG, GIF, PNG, BMP mặc định là JPG
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function show ($type = NULL)
    {
        //Lấy ra kiểu file mặc định lấy từ tập tin gốc
        if($type === NULL)
            $type = $this->type;
        
        if (! isset($this->copy))
            throw new \Exception("No copy to show");
        switch ($type) {
            case GIF:
                header('Content-Type: image/gif');
                imagegif($this->copy, null);
                return true;
                break;
            case PNG:
                header('Content-Type: image/png');
                imagepng($this->copy, null, PNG_QUALITY);
                return true;
                break;
            case BMP:
                header('Content-Type: image/bmp');
                imagewbmp($this->copy, null);
                return true;
                break;
            case JPG:
            default:
                header('Content-Type: image/jpeg');
                imagejpeg($this->copy, null, JPG_QUALITY);
                return true;
                break;
        }
        throw new \Exception("Show failed");
    }

    public function __destruct ()
    {
        imagedestroy($this->image);
        if(is_resource($this->copy))
            imagedestroy($this->copy);
        $this->filename = null;
        $this->data = null;
    }

    function imagecreatefrombmp($filename )
    {
        $file = fopen( $filename, "rb" );
        $read = fread( $file, 10 );
        while( !feof( $file ) && $read != "" )
        {
            $read .= fread( $file, 1024 );
        }
        $temp = unpack( "H*", $read );
        $hex = $temp[1];
        $header = substr( $hex, 0, 104 );
        $body = str_split( substr( $hex, 108 ), 6 );
        if( substr( $header, 0, 4 ) == "424d" )
        {
            $header = substr( $header, 4 );
            // Remove some stuff?
            $header = substr( $header, 32 );
            // Get the width
            $width = hexdec( substr( $header, 0, 2 ) );
            // Remove some stuff?
            $header = substr( $header, 8 );
            // Get the height
            $height = hexdec( substr( $header, 0, 2 ) );
            unset( $header );
        }
        $x = 0;
        $y = 1;
        $image = imagecreatetruecolor( $width, $height );
        foreach( $body as $rgb )
        {
            $r = hexdec( substr( $rgb, 4, 2 ) );
            $g = hexdec( substr( $rgb, 2, 2 ) );
            $b = hexdec( substr( $rgb, 0, 2 ) );
            $color = imagecolorallocate( $image, $r, $g, $b );
            imagesetpixel( $image, $x, $height-$y, $color );
            $x++;
            if( $x >= $width )
            {
                $x = 0;
                $y++;
            }
        }
        return $image;
    }
}