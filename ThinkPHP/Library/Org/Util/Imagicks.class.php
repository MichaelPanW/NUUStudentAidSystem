<?php
class Imagicks
{
	private $image = null;
	private $type = null;

	// ���캯��
	public function __construct(){}


	// ��������
	public function __destruct()
	{
	    if($this->image!==null) $this->image->destroy(); 
	}

	// ����ͼ��
	public function open($path)
	{
		$this->image = new Imagick( $path );
		if($this->image)
		{
		    $this->type = strtolower($this->image->getImageFormat());
			$this->image->setImageCompressionQuality( 100 ); //����jpgѹ��������1 - 100
			$this->image->enhanceImage(); //ȥ���
		}
		return $this->image;
	}
	

	public function crop($x=0, $y=0, $width=null, $height=null)
	{
	    if($width==null) $width = $this->image->getImageWidth()-$x;
	    if($height==null) $height = $this->image->getImageHeight()-$y;
	    if($width<=0 || $height<=0) return;
	    
	    if($this->type=='gif')
	    {
            $image = $this->image;
	        $canvas = new Imagick();
	        
        	$images = $image->coalesceImages();
    	    foreach($images as $frame){
    	        $img = new Imagick();
    	        $img->readImageBlob($frame);
                $img->cropImage($width, $height, $x, $y);

                $canvas->addImage( $img );
                $canvas->setImageDelay( $img->getImageDelay() );
                $canvas->setImagePage($width, $height, 0, 0);
            }
            
            $image->destroy();
	        $this->image = $canvas;
	    }
	    else
	    {
	        $this->image->cropImage($width, $height, $x, $y);
	    }
	}

	/*
	* ����ͼ���С
	$fit: ��Ӧ��С��ʽ
	'force': ��ͼƬǿ�Ʊ��γ� $width X $height ��С
	'scale': �������ڰ�ȫ�� $width X $height ������ͼƬ, ������ź�ͼ���С ����ȫ���� $width X $height
	'scale_fill': �������ڰ�ȫ�� $width X $height ������ͼƬ����ȫ����û�����صĵط����ɫ, ʹ�ô˲���ʱ�����ñ������ɫ $bg_color = array(255,255,255)(��,��,��, ͸����) ͸����(0��͸��-127��ȫ͸��))
	����: ����ģ�� ����ͼ����ȡͼ����м䲿�� $width X $height ���ش�С
	$fit = 'force','scale','scale_fill' ʱ�� �������ͼ��
	$fit = ͼ��λֵ ʱ, ���ָ��λ�ò���ͼ�� 
	��ĸ��ͼ��Ķ�Ӧ��ϵ����:
	
	north_west   north   north_east
	
	west         center        east
	
	south_west   south   south_east
	
	*/
	public function resize_to($width = 100, $height = 100, $fit = 'center', $fill_color = array(255,255,255,0) )
	{
	    
	    switch($fit)
	    {
	        case 'force':
        	    if($this->type=='gif')
        	    {
        	        $image = $this->image;
        	        $canvas = new Imagick();
        	        
        	        $images = $image->coalesceImages();
            	    foreach($images as $frame){
            	        $img = new Imagick();
            	        $img->readImageBlob($frame);
                        $img->thumbnailImage( $width, $height, false );

                        $canvas->addImage( $img );
                        $canvas->setImageDelay( $img->getImageDelay() );
                    }
                    $image->destroy();
	                $this->image = $canvas;
        	    }
        	    else
        	    {
        	        $this->image->thumbnailImage( $width, $height, false );
        	    }
	            break;
	        case 'scale':
	            if($this->type=='gif')
        	    {
        	        $image = $this->image;
        	        $images = $image->coalesceImages();
        	        $canvas = new Imagick();
            	    foreach($images as $frame){
            	        $img = new Imagick();
            	        $img->readImageBlob($frame);
                        $img->thumbnailImage( $width, $height, false );

                        $canvas->addImage( $img );
                        $canvas->setImageDelay( $img->getImageDelay() );
                    }
                    $image->destroy();
	                $this->image = $canvas;
        	    }
        	    else
        	    {
        	        $this->image->thumbnailImage( $width, $height, false );
        	    }
	            break;
	        case 'scale_fill':
	            $size = $this->image->getImagePage(); 
	            $src_width = $size['width'];
	            $src_height = $size['height'];
	            
                $x = 0;
                $y = 0;
                
                $dst_width = $width;
                $dst_height = $height;

	    		if($src_width*$height > $src_height*$width)
				{
					$dst_height = intval($width*$src_height/$src_width);
					$y = intval( ($height-$dst_height)/2 );
				}
				else
				{
					$dst_width = intval($height*$src_width/$src_height);
					$x = intval( ($width-$dst_width)/2 );
				}

                $image = $this->image;
                $canvas = new Imagick();
                
                $color = 'rgba('.$fill_color[0].','.$fill_color[1].','.$fill_color[2].','.$fill_color[3].')';
        	    if($this->type=='gif')
        	    {
        	        $images = $image->coalesceImages();
            	    foreach($images as $frame)
            	    {
            	        $frame->thumbnailImage( $width, $height, true );

            	        $draw = new ImagickDraw();
                        $draw->composite($frame->getImageCompose(), $x, $y, $dst_width, $dst_height, $frame);

                        $img = new Imagick();
                        $img->newImage($width, $height, $color, 'gif');
                        $img->drawImage($draw);

                        $canvas->addImage( $img );
                        $canvas->setImageDelay( $img->getImageDelay() );
                        $canvas->setImagePage($width, $height, 0, 0);
                    }
        	    }
        	    else
        	    {
        	        $image->thumbnailImage( $width, $height, true );
        	        
        	        $draw = new ImagickDraw();
                    $draw->composite($image->getImageCompose(), $x, $y, $dst_width, $dst_height, $image);
                    
        	        $canvas->newImage($width, $height, $color, $this->get_type() );
                    $canvas->drawImage($draw);
                    $canvas->setImagePage($width, $height, 0, 0);
        	    }
        	    $image->destroy();
	            $this->image = $canvas;
	            break;
			default:
				$size = $this->image->getImagePage(); 
			    $src_width = $size['width'];
	            $src_height = $size['height'];
	            
                $crop_x = 0;
                $crop_y = 0;
                
                $crop_w = $src_width;
                $crop_h = $src_height;
                
	    	    if($src_width*$height > $src_height*$width)
				{
					$crop_w = intval($src_height*$width/$height);
				}
				else
				{
				    $crop_h = intval($src_width*$height/$width);
				}
                
			    switch($fit)
	            {
			    	case 'north_west':
			    	    $crop_x = 0;
			    	    $crop_y = 0;
			    	    break;
        			case 'north':
        			    $crop_x = intval( ($src_width-$crop_w)/2 );
        			    $crop_y = 0;
        			    break;
        			case 'north_east':
        			    $crop_x = $src_width-$crop_w;
        			    $crop_y = 0;
        			    break;
        			case 'west':
        			    $crop_x = 0;
        			    $crop_y = intval( ($src_height-$crop_h)/2 );
        			    break;
        			case 'center':
        			    $crop_x = intval( ($src_width-$crop_w)/2 );
        			    $crop_y = intval( ($src_height-$crop_h)/2 );
        			    break;
        			case 'east':
        			    $crop_x = $src_width-$crop_w;
        			    $crop_y = intval( ($src_height-$crop_h)/2 );
        			    break;
        			case 'south_west':
        			    $crop_x = 0;
        			    $crop_y = $src_height-$crop_h;
        			    break;
        			case 'south':
        			    $crop_x = intval( ($src_width-$crop_w)/2 );
        			    $crop_y = $src_height-$crop_h;
        			    break;
        			case 'south_east':
        			    $crop_x = $src_width-$crop_w;
        			    $crop_y = $src_height-$crop_h;
        			    break;
        			default:
        			    $crop_x = intval( ($src_width-$crop_w)/2 );
        			    $crop_y = intval( ($src_height-$crop_h)/2 );
	            }
	            
	            $image = $this->image;
	            $canvas = new Imagick();
	            
	    	    if($this->type=='gif')
        	    {
        	        $images = $image->coalesceImages();
            	    foreach($images as $frame){
            	        $img = new Imagick();
            	        $img->readImageBlob($frame);
                        $img->cropImage($crop_w, $crop_h, $crop_x, $crop_y);
                        $img->thumbnailImage( $width, $height, true );
                        
                        $canvas->addImage( $img );
                        $canvas->setImageDelay( $img->getImageDelay() );
                        $canvas->setImagePage($width, $height, 0, 0);
                    }
        	    }
        	    else
        	    {
        	        $image->cropImage($crop_w, $crop_h, $crop_x, $crop_y);
        	        $image->thumbnailImage( $width, $height, true );
        	        $canvas->addImage( $image );
        	        $canvas->setImagePage($width, $height, 0, 0);
        	    }
        	    $image->destroy();
	            $this->image = $canvas;
	    }
	    
	}
	

	

	// ���ˮӡͼƬ
	public function add_watermark($path, $x = 0, $y = 0)
	{
        $watermark = new Imagick($path);
        $draw = new ImagickDraw();
        $draw->composite($watermark->getImageCompose(), $x, $y, $watermark->getImageWidth(), $watermark->getimageheight(), $watermark);

	    if($this->type=='gif')
	    {
	        $image = $this->image;
            $canvas = new Imagick();
        	$images = $image->coalesceImages();
    	    foreach($image as $frame)
    	    {
                $img = new Imagick();
    	        $img->readImageBlob($frame);
                $img->drawImage($draw);
                
                $canvas->addImage( $img );
                $canvas->setImageDelay( $img->getImageDelay() );
            }
            $image->destroy();
	        $this->image = $canvas;
	    }
	    else
	    {
	        $this->image->drawImage($draw);
	    }
	}

	
	// ���ˮӡ����
	public function add_text($text, $x = 0 , $y = 0, $angle=0, $style=array())
	{
        $draw = new ImagickDraw();
        if(isset($style['font'])) $draw->setFont($style['font']);
        if(isset($style['font_size'])) $draw->setFontSize($style['font_size']);
	    if(isset($style['fill_color'])) $draw->setFillColor($style['fill_color']);
	    if(isset($style['under_color'])) $draw->setTextUnderColor($style['under_color']);
	    
	    if($this->type=='gif')
	    {
    	    foreach($this->image as $frame)
    	    {
    	        $frame->annotateImage($draw, $x, $y, $angle, $text);
    	    }
	    }
	    else
	    {
	        $this->image->annotateImage($draw, $x, $y, $angle, $text);
	    }
	}
	
	
	// ���浽ָ��·��
	public function save_to( $path )
	{
	    if($this->type=='gif')
	    {
	        $this->image->writeImages($path, true);
	    }
	    else
	    {
	        $this->image->writeImage($path);
	    }
	}

	// ���ͼ��
	public function output($header = true)
	{
	    if($header) header('Content-type: '.$this->type);
	    echo $this->image->getImagesBlob();		
	}

	
	public function get_width()
	{
        $size = $this->image->getImagePage(); 
        return $size['width'];
	}
	
	public function get_height()
	{
	    $size = $this->image->getImagePage(); 
        return $size['height'];
	}

	// ����ͼ�����ͣ� Ĭ����Դ����һ��
	public function set_type( $type='png' )
	{
	    $this->type = $type;
        $this->image->setImageFormat( $type );
	}

	// ��ȡԴͼ������
	public function get_type()
	{
		return $this->type;
	}


	// ��ǰ�����Ƿ�ΪͼƬ
	public function is_image()
	{
		if( $this->image )
			return true;
		else
			return false;
	}
	


	public function thumbnail($width = 100, $height = 100, $fit = true){ $this->image->thumbnailImage( $width, $height, $fit );} // ��������ͼ $fitΪ��ʱ�����ֱ������ڰ�ȫ�� $width X $height ����������ͼƬ

	/*
	���һ���߿�
	$width: ���ұ߿���
	$height: ���±߿���
	$color: ��ɫ: RGB ��ɫ 'rgb(255,0,0)' �� 16������ɫ '#FF0000' ����ɫ���� 'white'/'red'...
	*/
	public function border($width, $height, $color='rgb(220, 220, 220)')
	{
		$color=new ImagickPixel();
		$color->setColor($color);
		$this->image->borderImage($color, $width, $height);
	}
	
	public function blur($radius, $sigma){$this->image->blurImage($radius, $sigma);} // ģ��
	public function gaussian_blur($radius, $sigma){$this->image->gaussianBlurImage($radius, $sigma);} // ��˹ģ��
	public function motion_blur($radius, $sigma, $angle){$this->image->motionBlurImage($radius, $sigma, $angle);} // �˶�ģ��
	public function radial_blur($radius){$this->image->radialBlurImage($radius);} // ����ģ��

	public function add_noise($type=null){$this->image->addNoiseImage($type==null?imagick::NOISE_IMPULSE:$type);} // ������
	
	public function level($black_point, $gamma, $white_point){$this->image->levelImage($black_point, $gamma, $white_point);} // ����ɫ��
	public function modulate($brightness, $saturation, $hue){$this->image->modulateImage($brightness, $saturation, $hue);} // �������ȡ����Ͷȡ�ɫ��

	public function charcoal($radius, $sigma){$this->image->charcoalImage($radius, $sigma);} // ����
	public function oil_paint($radius){$this->image->oilPaintImage($radius);} // �ͻ�Ч��
	
	public function flop(){$this->image->flopImage();} // ˮƽ��ת
	public function flip(){$this->image->flipImage();} // ��ֱ��ת
	

	 /**
     +----------------------------------------------------------
     * ��������ͼ
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $image  ԭͼ
     * @param string $type ͼ���ʽ
     * @param string $thumbname ����ͼ�ļ���
     * @param string $maxWidth  ���
     * @param string $maxHeight  �߶�
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
	public function thumb($image,$thumbname,$type='',$maxWidth=200,$maxHeight=100)
	{
		Imagicks::open($image);  
		Imagicks::resize_to($maxWidth,$maxHeight, 'scale_fill');  
		//$image->add_text('1024i.com', 10, 20);  
		//$image->add_watermark('1024i.gif', 10, 50);  
		Imagicks::save_to($thumbname); 
		return $thumbname;
		//$this->image->borderImage($color, $width, $height);
	}
}