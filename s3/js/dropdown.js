Kanboard.Dropdown = function(app) {
    this.app = app;
};

// TODO: rewrite this code
Kanboard.Dropdown.prototype.listen = function() {
    var self = this;

    $(document).on('click', function() {
        self.close();
    });

    $(document).on('click', '.active-dropdown-menu', function() {
        self.close();
    });

    $(document).on('click', '.dropdown-menu', function(e) {	
        e.preventDefault();
        e.stopImmediatePropagation();
        self.close();
        $(this).removeClass('dropdown-menu');
        $(this).addClass('active-dropdown-menu');
        var submenu = $(this).next('ul');
        var offset = $(this).offset();
        // Clone the submenu outside of the column to avoid clipping issue with overflow
        //$("body").append(jQuery('<div style=" --color-primary: #333; --color-light: #999; --color-lighter: #dedede; --color-dark: #000; --color-medium: #555; --color-error: #b94a48; --link-color-primary: #36C; --link-color-focus: #DF5353; --link-color-hover: #333; --alert-color-default: #c09853; --alert-color-success: #468847; --alert-color-error: #b94a48; --alert-color-info: #3a87ad; --alert-color-normal: #333; --alert-background-color-default: #fcf8e3;--alert-background-color-success: #dff0d8; --alert-background-color-error: #f2dede; --alert-background-color-info: #d9edf7; --alert-background-color-normal: #f0f0f0; --alert-border-color-default: #fbeed5; --alert-border-color-success: #d6e9c6; --alert-border-color-error: #eed3d7; --alert-border-color-info: #bce8f1; --alert-border-color-normal: #ddd; --button-default-color: #333; --button-default-background-color: #f5f5f5; --button-default-border-color: #ddd; --button-default-color-focus: #000; --button-default-background-color-focus: #fafafa; --button-default-border-color-focus: #bbb; --button-primary-color: #fff; --button-primary-background-color: #4d90fe; --button-primary-border-color: #3079ed; --button-primary-color-focus: #fff; --button-primary-background-color-focus: #357ae8; --button-primary-border-color-focus: #3079ed; --button-danger-color: #fff; --button-danger-background-color: #d14836; --button-danger-border-color: #b0281a; --button-danger-color-focus: #fff; --button-danger-background-color-focus: #c53727; --button-danger-border-color-focus: #b0281a; --button-disabled-color: #ccc; --button-disabled-background-color: #f7f7f7; --button-disabled-border-color: #ccc; --avatar-color-letter: #fff; --activity-title-color: #000; --activity-title-border-color: #efefef; --activity-event-background-color: #fafafa; --activity-event-hover-color: #fff8dc; --user-mention-color: #000; --board-task-limit-color: #DF5353; font-size: 100%; color: var(--color-primary); font-family: Helvetica Neue,Helvetica,Arial,sans-serif; text-rendering: optimizeLegibility; font-weight: initial; line-height: initial; text-align: initial; box-sizing: initial;">', {"id": "dropdown"}));
        //$("body").append(jQuery("<div>", {"id": "dropdown"}));
		$("body").append(jQuery('<div>', {"id": "dropdown", "style": "--color-primary: #333; --color-light: #999; --color-lighter: #dedede; --color-dark: #000; --color-medium: #555; --color-error: #b94a48; --link-color-primary: #36C; --link-color-focus: #DF5353; --link-color-hover: #333; --alert-color-default: #c09853; --alert-color-success: #468847; --alert-color-error: #b94a48; --alert-color-info: #3a87ad; --alert-color-normal: #333; --alert-background-color-default: #fcf8e3;--alert-background-color-success: #dff0d8; --alert-background-color-error: #f2dede; --alert-background-color-info: #d9edf7; --alert-background-color-normal: #f0f0f0; --alert-border-color-default: #fbeed5; --alert-border-color-success: #d6e9c6; --alert-border-color-error: #eed3d7; --alert-border-color-info: #bce8f1; --alert-border-color-normal: #ddd; --button-default-color: #333; --button-default-background-color: #f5f5f5; --button-default-border-color: #ddd; --button-default-color-focus: #000; --button-default-background-color-focus: #fafafa; --button-default-border-color-focus: #bbb; --button-primary-color: #fff; --button-primary-background-color: #4d90fe; --button-primary-border-color: #3079ed; --button-primary-color-focus: #fff; --button-primary-background-color-focus: #357ae8; --button-primary-border-color-focus: #3079ed; --button-danger-color: #fff; --button-danger-background-color: #d14836; --button-danger-border-color: #b0281a; --button-danger-color-focus: #fff; --button-danger-background-color-focus: #c53727; --button-danger-border-color-focus: #b0281a; --button-disabled-color: #ccc; --button-disabled-background-color: #f7f7f7; --button-disabled-border-color: #ccc; --avatar-color-letter: #fff; --activity-title-color: #000; --activity-title-border-color: #efefef; --activity-event-background-color: #fafafa; --activity-event-hover-color: #fff8dc; --user-mention-color: #000; --board-task-limit-color: #DF5353; font-size: 100%; color: var(--color-primary); font-family: Helvetica Neue,Helvetica,Arial,sans-serif; text-rendering: optimizeLegibility; font-weight: initial; line-height: initial; text-align: initial; box-sizing: initial;"}));

		submenu.clone().appendTo("#dropdown");

        var clone = $("#dropdown ul");
        clone.addClass('dropdown-submenu-open');
		
		clone.css('z-index', 1000);
		clone.css('display', 'block');

        var submenuHeight = clone.outerHeight();
        var submenuWidth = clone.outerWidth();
		
        if (offset.top + submenuHeight - $(window).scrollTop() < $(window).height() || $(window).scrollTop() + offset.top < submenuHeight) {
            clone.css('top', offset.top + $(this).height());
        }
        else {
            clone.css('top', offset.top - submenuHeight - 5);
        }

        if (offset.left + submenuWidth > $(window).width()) {
            var newOffset = offset.left - submenuWidth + $(this).outerWidth();
            // If calculated left offset is negative (off-screen), default to 15 pixels
            if (newOffset < 0) {
                newOffset = 15;
            }
            clone.css('left', newOffset);
        }
        else {
            clone.css('left', offset.left);
        }

        if (document.getElementById('dropdown') !== null) {
            KB.trigger('dropdown.afterRender');
        }
    });

    $(document).on('click', '.dropdown-submenu-open li', function(e) {
    	
        if ($(e.target).is('li')) {
            KB.trigger('dropdown.clicked');

            var element = $(this).find('a:visible');

            if (element.length > 0) {
                element[0].click(); // Calling native click() not the jQuery one
            }
        }
    });
};

Kanboard.Dropdown.prototype.close = function() {
    if (document.getElementById('dropdown') !== null) {
        KB.trigger('dropdown.beforeDestroy');
    }

    $('.active-dropdown-menu').addClass('dropdown-menu');
    $('.active-dropdown-menu').removeClass('active-dropdown-menu');
    $("#dropdown").remove();
};
