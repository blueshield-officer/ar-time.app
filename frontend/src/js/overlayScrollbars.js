addEventListener("DOMContentLoaded", () => {
    var {
        OverlayScrollbars,
        ScrollbarsHidingPlugin,
        SizeObserverPlugin,
        ClickScrollPlugin
    } = OverlayScrollbarsGlobal;

    const scrollElements = ['target']
    scrollElements.forEach(scrollElements => {
        if (document.getElementById(scrollElements)){
            OverlayScrollbars(document.querySelector(`#${scrollElements}`), {
                paddingAbsolute: false,
                showNativeOverlaidScrollbars: false,
                update: {
                    elementEvents: [['img', 'load']],
                    debounce: [0, 33],
                    attributes: null,
                    ignoreMutation: null,
                },
                overflow: {
                    x: 'hidden',
                    y: 'scroll',
                },
                scrollbars: {
                    theme: 'os-theme-dark',
                    visibility: 'auto',
                    autoHide: 'leave',
                    autoHideDelay: 700,
                    autoHideSuspend: false,
                    dragScroll: true,
                    clickScroll: false,
                    pointers: ['mouse', 'touch', 'pen'],
                },
            });
        }
    })
})