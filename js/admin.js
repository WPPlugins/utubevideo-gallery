var utvAdmin = {};

utvAdmin.initialize = function($){

    utvAdmin.setupObjects($);
    utvAdmin.setupFunctions($);
    utvAdmin.setupEvents($);

};

utvAdmin.setupEvents = function($){

    $(function(){

        //load video data for video edits into videomemory object
        if($('#utv-vid-data').length){

            var raw = $('#utv-vid-data').val().split(':');

            if(raw.length = 2)
                utvAdmin.videomemory.vid = raw[0];
                utvAdmin.videomemory.source = raw[1];

        }

        //confirm and error messages delay and removal
        $('div.updated, div.e-message').delay(6000).queue(function(){$(this).remove();});

        //form validation
        $('#utv-videoadd-submit, #saveAlbum, #createGallery, #saveAlbumEdit, #saveGalleryEdit, #saveVideoEdit').click(function(){

            var form = $(this).parents('form');
            if(utvAdmin.checkForm(form))
                return false;

        });

        //add video - edit video shared functions
        $('#utv-videoadd-url').change(function(){

            var source = $('#utv-videoadd-source').val();
            var rawurl = $(this).val();

            utvAdmin.videomemory.autoplay = 0;

            if(rawurl != ''){

                if(source == 'youtube'){

                    utvAdmin.videomemory.source = 'youtube';

                    var parse = rawurl.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);

                    if(parse != null)
                        utvAdmin.videomemory.vid = parse[1];

                }else if(source == 'vimeo'){

                    utvAdmin.videomemory.source = 'vimeo';

                    var parse = rawurl.match(/https?:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/);

                    if(parse != null)
                        utvAdmin.videomemory.vid = parse[2];

                }

            }else{

                utvAdmin.videomemory.source = '';
                utvAdmin.videomemory.vid = '';

            }

            $('#utv-video-preview').attr('src', utvAdmin.generateEmbedUrl());

        });

        $('#utv-videoadd-starttime').change(function(){

            var val = $(this).val();

            if(!isNaN(val)){
                utvAdmin.videomemory.starttime = val;
                utvAdmin.videomemory.autoplay = 1;
            }
            else{
                utvAdmin.videomemory.starttime = '';
                $(this).val('');
            }

            $('#utv-video-preview').attr('src', utvAdmin.generateEmbedUrl());

        });

        $('#utv-videoadd-endtime').change(function(){

            var val = $(this).val();

            if(!isNaN(val)){
                utvAdmin.videomemory.endtime = val;
                utvAdmin.videomemory.autoplay = 1;
            }
            else{
                utvAdmin.videomemory.endtime = '';
                $(this).val('');
            }

            $('#utv-video-preview').attr('src', utvAdmin.generateEmbedUrl());

        });

        $('#utv-videoadd-source').change(function(){

            var source = $(this).val();

            utvAdmin.videomemory.autoplay = 0;

			//check if preview needs refreshed
			if(source != utvAdmin.videomemory.source){

				utvAdmin.videomemory.source = source;
				$('#utv-video-preview').attr('src', utvAdmin.generateEmbedUrl());

			}

			//change visible form fields based on video type
            if(source == 'youtube'){

                $('#utv-videoadd-quality').parent('p').removeClass('utv-hide');
                $('#utv-videoadd-chrome').parent('p').removeClass('utv-hide');
                $('#utv-videoadd-endtime').parent('p').removeClass('utv-hide');

            }else if(source == 'vimeo'){

                $('#utv-videoadd-quality').parent('p').addClass('utv-hide');
                $('#utv-videoadd-chrome').parent('p').addClass('utv-hide');
                $('#utv-videoadd-endtime').parent('p').addClass('utv-hide');

            }

        });

        //edit album

        $('#utv-album-thumb-select').on('click', '.utv-album-thumb-choice', function(){

            var self = $(this);

            $('#utv-album-thumb-select .utv-album-thumb-choice').removeClass('utv-album-thumb-choice-active');
            self.addClass('utv-album-thumb-choice-active');

            var imgsrc = self.find('img').attr('src');
            var thumb = imgsrc.replace(utvJSData.thumbCacheDirectory, "");
            thumb = thumb.replace('.jpg', '');

            $('#utv-album-selected-thumb').val(thumb);

            $('#utv-album-preview-thumb').attr('src', imgsrc);

        });

        //options panel

        $('#utv-reset-overlay-color').click(function(){

            $('#utv-overlay-color').val('#000');
            return false;

        });

        $('#utv-reset-overlay-opacity').click(function(){

            $('#utv-overlay-opacity').val('0.85');
            return false;

        });

        $('#utv-reset-thumbnail-width').click(function(){

            $('#utv-thumbnail-width').val('150');
            return false;

        });

        $('#utv-reset-thumbnail-padding').click(function(){

            $('#utv-thumbnail-padding').val('10');
            return false;

        });

        $('#utv-reset-thumbnail-borderradius').click(function(){

            $('#utv-thumbnail-borderradius').val('3');
            return false;

        });

        $('#utv-reset-width').click(function(){

            $('#utv-player-width').val('950');
            $('#utv-player-height').val('537');
            return false;

        });

        $('#utv-player-width').keyup(function(){

            $('#utv-player-height').val(Math.round($('#utv-player-width').val() / 1.77));

        });

        $('#utv-player-height').keyup(function(){

            $('#utv-player-width').val(Math.round($('#utv-player-height').val() * 1.77));

        });

        //overview galleries

        $('.ut-delete-gallery').click(function(){

            if(!confirm(utvJSData.translations.confirmGalleryDelete))
                return false;

            var $item = $(this).parents('tr');
            var key = $item.attr('id');

            var data =
            {
                action: 'ut_deletegallery',
                key: key,
                nonce: utvJSData.nonces.deleteGallery
            };

            $.post(ajaxurl, data, function(response) {

                if(response)
                    $item.fadeOut(400, function(){ $item.remove(); });

            });

            return false;

        });

        //view album

        $('#utv-view-album .utv-sortable-table tbody').sortable({
            placeholder: 'utv-sortable-placeholder',
            handle: '.utv-sortable-handle',
            opacity: .8,
            containment: 'parent',
            stop: function(event, ui){

                var ordering = $('#utv-view-album .utv-sortable-table tbody').sortable('toArray').toString();

                var data =
                {
                    action: 'utv_videoorderupdate',
                    order: ordering
                };

                $.post(ajaxurl, data, function(response) {});

            }
        }).disableSelection();

        $('.ut-delete-video').click(function(){

            if(!confirm(utvJSData.translations.confirmVideoDelete))
                return false;

            var $item = $(this).parents('tr');
            var key = $item.attr('id');
            var $counter = $('#utv-video-count');

            var data =
            {
                action: 'ut_deletevideo',
                key: key,
                nonce: utvJSData.nonces.deleteVideo
            };

            $.post(ajaxurl, data, function(response) {

                if(response){

                    $item.fadeOut(400, function(){ $item.remove(); });
                    $counter.text($counter.text() - 1);

                }

            });

            return false;
        });

        $('#utv-view-album .utv-publish, #utv-view-album .utv-unpublish').click(function(){

            var self = $(this);
            var key = self.parents('tr').attr('id');
            var changeto = (self.hasClass('utv-publish') ? '0' : '1');

            var data =
            {
                action: 'ut_publishvideo',
                key: key,
                changeTo: changeto,
                nonce: utvJSData.nonces.publishVideo
            };

            utvAdmin.ajaxPubUnPubStatus(data, self);

            return false;

        });

        //view gallery

        $('#utv-view-gallery .utv-sortable-table tbody').sortable({
            placeholder: 'utv-sortable-placeholder',
            handle: '.utv-sortable-handle',
            opacity: .8,
            containment: 'parent',
            stop: function(event, ui){

                var ordering = $('#utv-view-gallery .utv-sortable-table tbody').sortable('toArray').toString();

                var data =
                {
                    action: 'utv_albumorderupdate',
                    order: ordering
                };

                $.post(ajaxurl, data, function(response) {});

            }
        }).disableSelection();

        $('.ut-delete-album').click(function(){

            if(!confirm(utvJSData.translations.confirmAlbumDelete))
                return false;

            var $item = $(this).parents('tr');
            var key = $item.attr('id');

            var data =
            {
                action: 'ut_deletealbum',
                key: key,
                nonce: utvJSData.nonces.deleteAlbum
            };

            $.post(ajaxurl, data, function(response) {

                if(response)
                    $item.fadeOut(400, function(){ $item.remove(); });

            });

            return false;
        });

        $('#utv-view-gallery .utv-publish, #utv-view-gallery .utv-unpublish').click(function(){

            var self = $(this);
            var key = self.parents('tr').attr('id');
            var changeto = (self.hasClass('utv-publish') ? '0' : '1');

            var data =
            {
                action: 'ut_publishalbum',
                key: key,
                changeTo: changeto,
                nonce: utvJSData.nonces.publishAlbum
            };

            utvAdmin.ajaxPubUnPubStatus(data, self);

            return false;

        });

        //add playlist

        $('#utv-playlistadd-source').change(function(){

            var source = $(this).val();
            var rawurl = $('#utv-playlistadd-url').val();
            var action = '';

            //change out form fields based on selection
            if(source == 'youtube'){

                $('#utv-playlistadd-quality').parent('p').removeClass('utv-hide');
                $('#utv-playlistadd-chrome').parent('p').removeClass('utv-hide');
                action = 'utv_fetchyoutubeplaylist';

            }else if(source == 'vimeo'){

                $('#utv-playlistadd-quality').parent('p').addClass('utv-hide');
                $('#utv-playlistadd-chrome').parent('p').addClass('utv-hide');
                action = 'utv_fetchvimeoplaylist';

            }

			//fetch new preview data if applicable
		    if(rawurl != '' && source != utvAdmin.playlistmemory.source){

			   	 //disable form submission
			   	 $('#utv-add-playlist').attr('disabled', 'disabled');

			   	 //clear old preview data
			   	 $('#utv-playlist-preview').html('');
			   	 $('#utv-playlist-preview-loader').removeClass('utv-hide');

			   	 //load playlist preview
			   	 utvAdmin.ajaxRetrievePlaylistData(rawurl, action);

		    }

			//update playlist source
		    utvAdmin.playlistmemory.source = source;

        });

		$('#utv-playlistadd-url').change(function(){

            var rawurl = $(this).val();
            var source = $('#utv-playlistadd-source').val();

			//clear old preview data
			$('#utv-playlist-preview').html('');
			$('#utv-playlist-preview-selectedcount, #utv-playlist-preview-totalcount').text('0');
			$('#utv-add-playlist').removeAttr('disabled');

            if(rawurl != ''){

				//disable form submission
				$('#utv-add-playlist').attr('disabled', 'disabled');

				//show loader
                $('#utv-playlist-preview-loader').removeClass('utv-hide');

                //fetch new preview data
                if(source == 'youtube')
                    utvAdmin.ajaxRetrievePlaylistData(rawurl, 'utv_fetchyoutubeplaylist');
                else
                    utvAdmin.ajaxRetrievePlaylistData(rawurl, 'utv_fetchvimeoplaylist');

            }

        });

        $('#utv-playlist-preview').on('click', '.utv-playlist-choice', function(){

            var self = $(this);

            if(self.hasClass('utv-playlist-choice-active')){

                utvAdmin.playlistmemory.selectedvideos--;
                self.removeClass('utv-playlist-choice-active');

            }
            else{

                utvAdmin.playlistmemory.selectedvideos++;
                self.addClass('utv-playlist-choice-active');

            }

            $('#utv-playlist-preview-selectedcount').text(utvAdmin.playlistmemory.selectedvideos);

        });

        $('#utv-add-playlist').click(function(){

            var form = $(this).parents('form');
            var choices = $('#utv-playlist-preview .utv-playlist-choice-active');

            var ids = '';
            var titles = '';
            var thumbURLs = '';

            $.each(choices, function(key, val){

                var form = $(val).next();
                ids += $(form).children('.utv-playlist-item-id').val() + '||';
                titles += $(form).children('.utv-playlist-item-title').val() + '||';
                thumbURLs += $(form).children('.utv-playlist-thumburl').val() + '||';

            });

            if(ids.length > 0)
                ids = ids.slice(0, -2);

            if(titles.length > 0)
                titles = titles.slice(0, -2);

            if(thumbURLs.length > 0)
                thumbURLs = thumbURLs.slice(0, -2);

            $('#utv-playlist-add-ids').val(ids);
            $('#utv-playlist-add-titles').val(titles);
            $('#utv-playlist-add-thumburls').val(thumbURLs);

            if(utvAdmin.checkForm(form))
                return false;

        });

    });

};

utvAdmin.setupObjects = function($){

    this.videomemory = {

        'source': '',
        'vid': '',
        'starttime': '',
        'endtime': '',
        'autoplay': '0'

    }

    this.playlistmemory = {

		'source': '',
        'selectedvideos': ''

    }

};

utvAdmin.setupFunctions = function($){

    this.checkTextField = function(field){

        if(field.val() == ''){
            field.addClass('utv-invalid-field');
            return true;
        }
        else
            field.removeClass('utv-invalid-field');

    }

    this.checkForm = function(form){

        var issues = 0;

        form.find('input[type="text"].utv-required').each(function(){
            if(utvAdmin.checkTextField($(this)))
                issues++;
        });

        if(issues > 0)
            return true;
    }

    this.generateEmbedUrl = function(){

        if(utvAdmin.videomemory.source == 'youtube')
            return 'https://www.youtube.com/embed/' + utvAdmin.videomemory.vid + '?modestbranding=1&rel=0&showinfo=0&autohide=0&iv_load_policy=3&color=white&theme=dark&rand=' + utvAdmin.generateRandomInt() + '&autoplay=' + utvAdmin.videomemory.autoplay + '&start=' + utvAdmin.videomemory.starttime + '&end=' + utvAdmin.videomemory.endtime;
        else if(utvAdmin.videomemory.source == 'vimeo')
            return 'https://player.vimeo.com/video/' + utvAdmin.videomemory.vid + '?&title=0&portrait=0&byline=0badge=0&rand=' + utvAdmin.generateRandomInt() + '&autoplay=' + utvAdmin.videomemory.autoplay + '#t=' + utvAdmin.videomemory.starttime;
        else
            return '';

    }

    this.generateRandomInt = function(){

        return Math.floor(Math.random() * 1000);

    }

    this.ajaxPubUnPubStatus = function(data, self){

        $.post(ajaxurl, data, function(response) {

            if(response){
                if(self.hasClass('utv-publish')){
                    self.addClass('utv-unpublish');
                    self.removeClass('utv-publish');
                }else{
                    self.addClass('utv-publish');
                    self.removeClass('utv-unpublish');
                }

            }

        });

    }

    this.ajaxRetrievePlaylistData = function(url, action){

        if(utvAdmin.notNull(url) && utvAdmin.notNull(action)){

            var data = {

                'action': action,
                'url': url,
                'nonce': utvJSData.nonces.retrievePlaylist

            }

            $.post(ajaxurl, data, function(rsp) {

                if(rsp && rsp.valid && rsp.data.length > 0){

                    utvAdmin.playlistmemory.selectedvideos = rsp.data.length;

                    var htmlstring = '';

                    for(var i = 0; i < rsp.data.length; i++){

                        htmlstring += '<div class="utv-playlist-preview-item">\
                            <span class="utv-playlist-preview-item-num">' + (i + 1) + ')</span>\
                            <div class="utv-playlist-choice utv-playlist-choice-active">\
                                <img src="' + rsp.data[i].thumbURL + '"/>\
                                <span class="utv-playlist-choice-overlay"></span>\
                            </div>\
                            <div class="utv-playlist-preview-form">\
                                <input type="text" class="utv-playlist-item-title" value="' + rsp.data[i].title + '"/>\
                                <span class="utv-playlist-item-duration">' + rsp.data[i].duration + '</span>\
                                <input type="hidden" class="utv-playlist-item-id" value="' + rsp.data[i].videoId + '"/>\
                                <input type="hidden" class="utv-playlist-thumburl" value="' + rsp.data[i].thumbURL + '"/>\
                            </div>\
                        </div>';

                    }

                    $('#utv-playlist-preview-loader').addClass('utv-hide');
                    $('#utv-playlist-preview').html(htmlstring);
                    $('#utv-playlist-preview-selectedcount, #utv-playlist-preview-totalcount').text(rsp.data.length);
                    utvAdmin.setErrorMessage($('#utv-add-playlist-message'), '');

					//reenable form submission
					$('#utv-add-playlist').removeAttr('disabled');

                }else{
                    $('#utv-playlist-preview-loader').addClass('utv-hide');
					$('#utv-playlist-preview-selectedcount, #utv-playlist-preview-totalcount').text('0');
                    utvAdmin.setErrorMessage($('#utv-add-playlist-message'), rsp.message);
                }

            });

        }

    }

    this.notNull = function(data){

        if(data != undefined && data != '')
            return true;
        else
            return false;

    }

    this.setErrorMessage = function(selector, message){

        if(utvAdmin.notNull(selector))
            selector.text(message);

    }

};

//document.ready
(function($){

    utvAdmin.initialize($);

})(jQuery);
