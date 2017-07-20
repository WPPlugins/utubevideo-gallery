var utvFrontend = {};

utvFrontend.initialize = function($){

	utvFrontend.setupObjects($);
    utvFrontend.setupEvents($);

};

utvFrontend.setupEvents = function($){

	$(function(){

		var galleries = $('.utv-container');
		var panels = $('.utv-panel');

		$.each(galleries, function(mark, data){

			utvFrontend.memory.galleries.push(new utvFrontend.Gallery(data));

		});

		$.each(panels, function(mark, data){

			utvFrontend.memory.panels.push(new utvFrontend.Panel(data));

		});

	});

};

utvFrontend.setupObjects = function($){

	this.memory = {

		'galleries': new Array(),
		'panels': new Array(),
		'thumbwidth': parseInt(utvJSData.thumbnailWidth),
		'thumbpadding': parseInt(utvJSData.thumbnailPadding)

	};

	this.Gallery = function(container){

		//properties
		this.outercontainer = container;
		this.innercontainer = $(this.outercontainer).find('.utv-inner-wrapper');

		//methods
		this.setGalleryFlow = function(){

			var outerwidth = $(this.outercontainer).width();
			var blocks = Math.floor(outerwidth / (utvFrontend.memory.thumbwidth + (utvFrontend.memory.thumbpadding * 2)));
			var size = (utvFrontend.memory.thumbwidth + (utvFrontend.memory.thumbpadding * 2)) * blocks;
			$(this.innercontainer).css('width', size + 'px');
			$(this.outercontainer).removeClass('utv-invis');

		};

		//setup panel flow
		this.setGalleryFlow();

		//window resize event
		$(window).resize({'classRoot': this}, function(e){

			e.data.classRoot.setGalleryFlow();

		});

		//video lightbox events
		$(this.outercontainer).on('click', 'a.utv-popup', function(){

			var url = $(this).attr('href');
			var title = $(this).attr('title');

			$.magnificPopup.open({
				items: {src: url},
				type: 'iframe',
				iframe: {
					patterns: new Array(),
					markup: '<div class="utv-mfp-iframe-scaler mfp-iframe-scaler">'+
						'<div class="mfp-close"></div>'+
						'<iframe class="mfp-iframe" frameborder="0" width="' + utvJSData.playerWidth + '" height="' + utvJSData.playerHeight + '" allowfullscreen></iframe>'+
						'</div><div class="utv-mfp-bottom-bar">'+
						'<div class="mfp-title"></div></div>'
				},
				key: 'utvid',
				callbacks: {
					open: function() {
						$('.mfp-content').css('maxWidth', utvJSData.playerWidth + 'px');
						$('.mfp-title').text(title);
						var $bg = $('.mfp-bg');
						$bg.css('background', utvJSData.lightboxOverlayColor);
						$bg.css('opacity', utvJSData.lightboxOverlayOpacity);
					}
				}
			});

			return false;

		});

	};

	this.Panel = function(container){

		//properties
		this.outercontainer = container;
		this.innercontainer = $(this.outercontainer).find('.utv-inner-wrapper');
		this.panelTitle = $(this.outercontainer).find('.utv-video-panel-title');
		this.panelPlayer = $(this.outercontainer).find('.utv-video-panel-iframe');
		this.panelVideos = $(this.innercontainer).find('.utv-thumb');
		this.pagingContainer = $(this.innercontainer).find('.utv-video-panel-paging');
		this.pagingHandles = $(this.pagingContainer).find('.utv-panel-paging-handle');
		this.backArrow = $(this.outercontainer).find('.utv-video-panel-bkarrow');
		this.forwardArrow = $(this.outercontainer).find('.utv-video-panel-fwarrow');

		this.panelVideoCount = parseInt($(this.outercontainer).data('panel-video-count'));
		this.totalPages = Math.ceil(this.panelVideos.length / this.panelVideoCount);
		this.visibleControls = $(this.outercontainer).data('visibleControls');
		this.currentPage = 1;

		//methods
		this.setPanelFlow = function(){

			var outerwidth = $(this.outercontainer).width();
			var blocks = Math.floor(outerwidth / (utvFrontend.memory.thumbwidth + (utvFrontend.memory.thumbpadding * 2)));
			var size = (utvFrontend.memory.thumbwidth + (utvFrontend.memory.thumbpadding * 2)) * blocks;
			$(this.innercontainer).css('width', size + 'px');
			$(this.outercontainer).removeClass('utv-invis');

		};

		this.showHidePanelVideos = function(startIndex){

			var endIndex = (startIndex + this.panelVideoCount) - 1;

			$.each(this.panelVideos, function(marker, video){

				if(marker < startIndex || marker > endIndex)
					$(video).addClass('utv-hide');
				else
					$(video).removeClass('utv-hide');

			});

		};

		this.changePanelVideo = function(video){

			//get info
			var type = video.data('type');
			var id = video.data('id');
			var name = video.data('name');
			var index = video.data('index');
			var stime = video.data('stime');
			var etime = video.data('etime');

			//change video displayed
			var link = '';

			if (type == 'youtube')
				link = 'https://www.youtube.com/embed/' + id + '?modestbranding=1&rel=0&showinfo=0&autohide=1&controls=' + (this.visibleControls == true ? '1' : '0') + '&theme=' + utvJSData.playerControlTheme + '&color=' + utvJSData.playerProgressColor + '&autoplay=1&iv_load_policy=3&start=' + stime + '&end=' + etime;
			else if (type == 'vimeo')
				link = 'https://player.vimeo.com/video/' + id + '?autoplay=1&autopause=0&title=0&portrait=0&byline=0&badge=0#t=' + stime;

			$(this.panelPlayer).attr('src', link);
			$(this.panelTitle).text(name);

			//check to make sure on right page
			var page = Math.floor(index / this.panelVideoCount) + 1;

			if(page != this.currentPage){

				//switch page handle
				$(this.pagingHandles).removeClass('utv-panel-paging-active');

				$.each($(this.pagingHandles), function(mark, data){

					if($(data).text() == page)
						$(data).addClass('utv-panel-paging-active');

				});

				this.currentPage = page;

				//change page view
				var startIndex = (page - 1) * this.panelVideoCount;
				this.showHidePanelVideos(startIndex);

			}

		};

		//setup panel flow
		this.setPanelFlow();

		//set panel video view
		this.showHidePanelVideos(0);

		//window resize event
		$(window).resize({'classRoot': this}, function(e){

			e.data.classRoot.setPanelFlow();

		});

		//video click events
		$(this.innercontainer).on('click', '.utv-thumb', {'classRoot': this}, function(e){

			var self = $(this);

			$(e.data.classRoot.panelVideos).removeClass('utv-panel-video-active');
			self.addClass('utv-panel-video-active');

			e.data.classRoot.changePanelVideo(self);

		});

		//paging events
		this.pagingContainer.on('click', '.utv-panel-paging-handle', {'classRoot': this}, function(e){

			var self = $(this);

			var startIndex = (parseInt(self.text()) - 1) * e.data.classRoot.panelVideoCount;
			e.data.classRoot.currentPage = self.text();

			e.data.classRoot.showHidePanelVideos(startIndex);

			$(e.data.classRoot.pagingContainer).find('.utv-panel-paging-handle').removeClass('utv-panel-paging-active');
			self.addClass('utv-panel-paging-active');

		});

		//back arrow event
		$(this.backArrow).click({'classRoot': this}, function(e){

			var activevideo = $(e.data.classRoot.panelVideos).filter('.utv-panel-video-active');
			var prevvideo = $(activevideo).prev();

			if(prevvideo.length > 0){

				activevideo.removeClass('utv-panel-video-active');
				prevvideo.addClass('utv-panel-video-active');

				e.data.classRoot.changePanelVideo(prevvideo);

			}

		});

		//forward arrow event
		$(this.forwardArrow).click({'classRoot': this}, function(e){

			var activevideo = $(e.data.classRoot.panelVideos).filter('.utv-panel-video-active');
			var nextvideo = $(activevideo).next();

			if(nextvideo.length > 0){

				activevideo.removeClass('utv-panel-video-active');
				nextvideo.addClass('utv-panel-video-active');

				e.data.classRoot.changePanelVideo(nextvideo);

			}

		});

	};

};

//document.ready
(function($){

    utvFrontend.initialize($);

})(jQuery);
