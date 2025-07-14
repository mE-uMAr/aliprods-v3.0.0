;(($) => {
  // Enhanced video functionality for frontend v3.0
  $(".aliprods-video-wrapper video, .aliprods-product-video-gallery video").each(function () {
    const $video = $(this)
    const $wrapper = $video.parent()

    // Add play/pause on click
    $video.on("click", () => {
      if (this.paused) {
        this.play()
      } else {
        this.pause()
      }
    })

    // Add loading state
    $video.on("loadstart", () => {
      $wrapper.addClass("loading")
    })

    $video.on("canplay", () => {
      $wrapper.removeClass("loading")
    })

    // Add error handling
    $video.on("error", () => {
      $wrapper.removeClass("loading")
      if (!$wrapper.find(".video-error").length) {
        $wrapper.append('<div class="video-error">Video could not be loaded</div>')
      }
    })

    // Pause other videos when one starts playing
    $video.on("play", function () {
      $("video")
        .not(this)
        .each(function () {
          this.pause()
        })
    })

    // Add keyboard controls
    $video.on("keydown", function (e) {
      switch (e.which) {
        case 32: // Space bar
          e.preventDefault()
          if (this.paused) {
            this.play()
          } else {
            this.pause()
          }
          break
        case 37: // Left arrow
          e.preventDefault()
          this.currentTime -= 10
          break
        case 39: // Right arrow
          e.preventDefault()
          this.currentTime += 10
          break
        case 38: // Up arrow
          e.preventDefault()
          this.volume = Math.min(1, this.volume + 0.1)
          break
        case 40: // Down arrow
          e.preventDefault()
          this.volume = Math.max(0, this.volume - 0.1)
          break
      }
    })

    // Make video focusable for keyboard controls
    $video.attr("tabindex", "0")

    // Add hover effects
    $wrapper.on("mouseenter", () => {
      if ($video[0].paused) {
        $video[0].play().catch(() => {
          // Auto-play prevented, ignore
        })
      }
    })

    $wrapper.on("mouseleave", () => {
      // Don't auto-pause, let user control
    })
  })

  // Enhanced image gallery for AliProds products
  $(".aliprods-enhanced").each(function () {
    const $img = $(this)

    // Add loading state
    $img.on("load", function () {
      $(this).addClass("loaded")
    })

    // Add error handling
    $img.on("error", function () {
      $(this).addClass("error")
    })
  })

  // Add smooth scrolling to video sections
  $('a[href*="#aliprods"]').on("click", function (e) {
    e.preventDefault()
    const target = $(this.getAttribute("href"))
    if (target.length) {
      $("html, body").animate(
        {
          scrollTop: target.offset().top - 100,
        },
        800,
      )
    }
  })
})(window.jQuery)
