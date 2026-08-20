
(function($) {
  'use strict';
  $(function() {
    var body = $('body');
    var mainWrapper = $('.main-wrapper');
    var footer = $('footer');
    var sidebar = $('.sidebar');
    var navbar = $('.navbar').not('.top-navbar');
    

    // Enable feather-icons with SVG markup
    feather.replace();


    // initialize clipboard plugin
    if ($('.btn-clipboard').length) {
      // Enabling tooltip to all clipboard buttons
      $('.btn-clipboard').attr('data-bs-toggle', 'tooltip').attr('title', 'Copy to clipboard');

      var clipboard = new ClipboardJS('.btn-clipboard');

      clipboard.on('success', function(e) {
        console.log(e);
        e.trigger.innerHTML = 'copied';
        setTimeout(function() {
          e.trigger.innerHTML = 'copy';
          e.clearSelection();
        },700)
      });
    }


    // initializing bootstrap tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })


    // initializing bootstrap popover
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl)
    })


    // Applying perfect-scrollbar 
    if ($('.sidebar .sidebar-body').length) {
      const sidebarBodyScroll = new PerfectScrollbar('.sidebar-body');
    }
    // commented beacuse of hang (scroll from  dropdown.html with small height)
    // if ($('.content-nav-wrapper').length) {
    //   const contentNavWrapper = new PerfectScrollbar('.content-nav-wrapper');
    // }


    // Close other submenu in sidebar on opening any
    sidebar.on('show.bs.collapse', '.collapse', function() {
      sidebar.find('.collapse.show').collapse('hide');
    });


    // Sidebar toggle to sidebar-folded
    $('.sidebar-toggler').on('click', function(e) {
      e.preventDefault();
      $('.sidebar-header .sidebar-toggler').toggleClass('active not-active');
      if (window.matchMedia('(min-width: 992px)').matches) {
        e.preventDefault();
        body.toggleClass('sidebar-folded');
      } else if (window.matchMedia('(max-width: 991px)').matches) {
        e.preventDefault();
        body.toggleClass('sidebar-open');
      }
    });


    // commmented because of apex chart width issue in desktop (in lg not in xl)
    // // sidebar-folded on large devices
    // function iconSidebar(e) {
    //   if (e.matches) {
    //     body.addClass('sidebar-folded');
    //   } else {
    //     body.removeClass('sidebar-folded');
    //   }
    // }
    // var desktopMedium = window.matchMedia('(min-width:992px) and (max-width: 1199px)');
    // desktopMedium.addListener(iconSidebar);
    // iconSidebar(desktopMedium);


    // Settings sidebar toggle
    $('.settings-sidebar-toggler').on('click', function(e) {
      $('body').toggleClass('settings-open');
    });


    // Sidebar theme settings
    $("input:radio[name=sidebarThemeSettings]").click(function() {
      $('body').removeClass('sidebar-light sidebar-dark');
      $('body').addClass($(this).val());
     })



    // Activate one navigation link using its complete URL path. Matching only the
    // last URL segment made related menus (for example all "golongan" links)
    // active at the same time.
    function normalizeNavigationPath(value) {
      try {
        var path = new URL(value, window.location.origin).pathname.replace(/\/+$/, '');
        return path || '/';
      } catch (error) {
        return '';
      }
    }

    function activateNavigation(links) {
      var currentPath = normalizeNavigationPath(window.location.href);
      var candidates = [];

      links.each(function() {
        var element = $(this);
        var href = element.attr('href');

        if (!href || href === '#' || href.indexOf('javascript:') === 0 || element.is('[data-bs-toggle]')) {
          return;
        }

        var path = normalizeNavigationPath(this.href || href);
        if (!path) {
          return;
        }

        var isExact = path === currentPath;
        var isParentPath = path !== '/' && currentPath.indexOf(path + '/') === 0;

        if (isExact || isParentPath) {
          candidates.push({
            element: element,
            path: path,
            exact: isExact
          });
        }
      });

      candidates.sort(function(a, b) {
        if (a.exact !== b.exact) {
          return a.exact ? -1 : 1;
        }

        return b.path.length - a.path.length;
      });

      var match = candidates[0];
      if (!match) {
        return;
      }

      var element = match.element;
      var collapse = element.closest('.collapse');

      element.addClass('active');
      element.closest('.nav-item').addClass('active');
      element.parents('.nav-item').last().addClass('active');

      if (collapse.length) {
        collapse.addClass('show');
        collapse.prev('[data-bs-toggle="collapse"]').attr('aria-expanded', 'true');
      }

      if (element.parents('.submenu-item').length) {
        element.addClass('active');
      }
    }

    sidebar.find('.nav-item.active, .nav-link.active').removeClass('active');
    sidebar.find('.collapse.show').removeClass('show');
    sidebar.find('[data-bs-toggle="collapse"]').attr('aria-expanded', 'false');
    activateNavigation($('.nav li a', sidebar));

    activateNavigation($('.horizontal-menu .nav li a'));


    //  open sidebar-folded when hover
    $(".sidebar .sidebar-body").hover(
    function () {
      if (body.hasClass('sidebar-folded')){
        body.addClass("open-sidebar-folded");
      }
    },
    function () {
      if (body.hasClass('sidebar-folded')){
        body.removeClass("open-sidebar-folded");
      }
    });


    // close sidebar when click outside on mobile/table    
    $(document).on('click touchstart', function(e){
      e.stopPropagation();

      // closing of sidebar menu when clicking outside of it
      if (!$(e.target).closest('.sidebar-toggler').length) {
        var sidebar = $(e.target).closest('.sidebar').length;
        var sidebarBody = $(e.target).closest('.sidebar-body').length;
        if (!sidebar && !sidebarBody) {
        if ($('body').hasClass('sidebar-open')) {
          $('body').removeClass('sidebar-open');
        }
        }
      }
    });


    //Horizontal menu in mobile
    $('[data-toggle="horizontal-menu-toggle"]').on("click", function() {
      $(".horizontal-menu .bottom-navbar").toggleClass("header-toggled");
    });
    // Horizontal menu navigation in mobile menu on click
    var navItemClicked = $('.horizontal-menu .page-navigation >.nav-item');
    navItemClicked.on("click", function(event) {
      if(window.matchMedia('(max-width: 991px)').matches) {
        if(!($(this).hasClass('show-submenu'))) {
          navItemClicked.removeClass('show-submenu');
        }
        $(this).toggleClass('show-submenu');
      }        
    })

    $(window).scroll(function() {
      if(window.matchMedia('(min-width: 992px)').matches) {
        var header = $('.horizontal-menu');
        if ($(window).scrollTop() >= 60) {
          $(header).addClass('fixed-on-scroll');
        } else {
          $(header).removeClass('fixed-on-scroll');
        }
      }
    });


    // Prevent body scrolling while sidebar scroll
    $('.sidebar .sidebar-body').hover(function () {
      $('body').addClass('overflow-hidden');
    }, function () {
      $('body').removeClass('overflow-hidden');
    });
   

  });
})(jQuery);
