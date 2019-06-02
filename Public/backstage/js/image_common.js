var flag=false; 
function DrawImage(ImgD,imageWidth,imageHeight) { 
   var image=new Image(); 
   image.src=ImgD.src; 
   if(image.width>0 && image.height>0){ 
    flag=true; 
    if(image.width/image.height>= imageWidth/imageHeight){ 
     if(image.width>imageWidth){ 
     ImgD.width=imageWidth; 
     ImgD.height=(image.height*imageWidth)/image.width; 
     }else{ 
     ImgD.width=image.width; 
     ImgD.height=image.height; 
     } 
     } 
    else{ 
     if(image.height>imageHeight){ 
     ImgD.height=imageHeight; 
     ImgD.width=(image.width*imageHeight)/image.height; 
     }else{ 
     ImgD.width=image.width; 
     ImgD.height=image.height; 
     } 
     } 
    } 
  }