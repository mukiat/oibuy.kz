export function compressImage(image){
	console.log(image)
	return new Promise((resolve, reject) => {
		plus.io.resolveLocalFileSystemURL(image,(entry)=> {
			entry.file((file)=>{
				console.log('压缩前getFile:'+JSON.stringify(file))
				if(file.size > 2019200) { //大于2mb
					plus.zip.compressImage({
						src:image,
						dst: image.replace('.png','test.png').replace('.PNG','text.PNG').replace('.jpg','text.jpg').replace('.JPG','text.JPG'),
						width:'40%',
						height:'40%',
						quality:10,
						overwrite:true,
						format:'jpg,png'
					},(event)=>{
						console.log('event:'+JSON.stringify(event));
						console.log('压缩后:'+event.size);
						console.log('压缩后:'+event.target);
						let newImage = image.replace('.png','test.png').replace('.PNG','text.PNG').replace('.jpg','text.jpg').replace('.JPG','text.JPG');
						console.log(newImage)
						resolve(newImage)
					},function(error){
						uni.showModal({
							content:'图片太大，需要请重新选择图片',
							showCancel:false
						})
					})
				}else{
					resolve(image)
				}
			})
		})
	},(e)=>{
		reject(e)
	})
}