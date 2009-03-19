package com.origo
{
    import flash.display.*;
    import flash.events.*;
    import flash.geom.Matrix;
    import flash.text.*;
    import flash.utils.*;
    import flash.filters.DropShadowFilter;
    
    import mx.core.BitmapAsset;
    import mx.events.*;
    import mx.preloaders.*;

    public class Preloader extends DownloadProgressBar
	{
		[Embed(source="../assets/logo.png")]
        [Bindable] private var logoClass:Class;
        
        [Embed(source="../assets/background.png")]
        [Bindable] private var backgroundClass:Class;
        
        private var logo:DisplayObject;
        private var background:Sprite;
        private var progressBar:Sprite;
        
        private var timer:Timer;
        private var fadeOutRate:Number = .005;
    
        public function Preloader()
        {   
            super();
            
            logo = new logoClass();
            logo.visible = false;
			addChild(logo);
			
			progressBar = new Sprite();
			progressBar.visible = false;
			var shadow:DropShadowFilter = new DropShadowFilter();
			shadow.distance = 3;
			shadow.angle = 45;
			shadow.alpha = 0.3;
			shadow.blurX = 4;
			shadow.blurY = 4;
			progressBar.filters = [shadow];
			addChild(progressBar);
        }
    
        override public function set preloader(preloader:Sprite):void 
        {
            // Listen for the relevant events
            preloader.addEventListener(ProgressEvent.PROGRESS, myHandleProgress);   
            preloader.addEventListener(FlexEvent.INIT_COMPLETE, myHandleInitEnd);
        }
    
        private function myHandleProgress(event:ProgressEvent):void 
        {
			// draw background
			var b:BitmapAsset = BitmapAsset(new backgroundClass());
			var tile:BitmapData = b.bitmapData;       
			var transform: Matrix = new Matrix();

			graphics.clear();
			graphics.beginBitmapFill(tile, transform, true);
			graphics.drawRect(0, 0, stageWidth, stageHeight);
        	
        	// set logo position
			logo.x = (stageWidth - logo.width) / 2;
			logo.y = (stageHeight - logo.height) / 2;
			logo.visible = true;
			
			// set progress bar position
			progressBar.visible = true;
			
        	var progressBarWidth:Number = 300;
        	var progressBarHeight:Number = 3;
        	var posX:Number = (stageWidth - progressBarWidth) / 2;
        	var posY:Number = stageHeight / 2 + 100;
        	
			// draw the progress bar
			var g:Graphics = progressBar.graphics;
			
			g.clear();
			
			g.beginFill(0xb0b0b0);
			g.lineStyle(1, 0xb0b0b0);
			g.drawRect(posX, posY, progressBarWidth, progressBarHeight);
			
			var progWidth:Number = progressBarWidth * event.bytesLoaded / event.bytesTotal;
			g.beginFill(0x1a60b0);
			g.lineStyle(1, 0x153252);
			g.drawRect(posX, posY, progWidth, progressBarHeight);
        }
    
        private function myHandleInitEnd(event:Event):void 
        {
            timer = new Timer(1);
            timer.addEventListener(TimerEvent.TIMER, closeScreenFade);
            timer.start();
        }
        
        public function closeScreenFade(event:TimerEvent):void
		{
			if(logo.alpha > 0){
				logo.alpha = logo.alpha - fadeOutRate;
				progressBar.alpha = progressBar.alpha - fadeOutRate;
				if(logo.alpha <= 0) {
					timer.stop();
					timer = new Timer(500, 1);
					timer.addEventListener(TimerEvent.TIMER, closeScreenFade);
					timer.start();
				}
			} 
			else {
				timer.stop();
				dispatchEvent(new Event(Event.COMPLETE));
			}        
        }
	}
}