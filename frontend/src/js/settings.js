function setTheme(theme) {
    if (getCookieValue('theme') === theme){
        return;
    }

    const themes = ['dark', 'light', 'system'];
    const themesNotUsed = themes.filter(el => el !== theme);


    const currentThemeIndicator = document.getElementById(theme + "-theme-switcher");
    const currentThemeIcon = document.getElementById(theme + "-theme-switcher-icon");
    currentThemeIndicator.classList.add('selected-theme')
    currentThemeIcon.src = currentThemeIcon.src.replace(".svg", "-fill.svg")


    themesNotUsed.forEach(themeNotUsed => {
        const localThemeIndicator = document.getElementById(themeNotUsed + "-theme-switcher")
        const localThemeIcon = document.getElementById(themeNotUsed + "-theme-switcher-icon")

        localThemeIndicator.classList.remove('selected-theme')
        if (localThemeIcon.src.includes('-fill.svg')) {
            localThemeIcon.src = (localThemeIcon.src).replace("-fill", '');
        }
    })


    document.cookie = "theme=" + theme;

    if (['dark', 'light'].includes(theme)) {
        document.getElementById("theme").href = '/frontend/dist/css/theme-' + theme + '.min.css';
    }
    else {
        console.log(getUserTheme())
        document.getElementById("theme").href = '/frontend/dist/css/theme-' + getUserTheme() + '.min.css';
    }
}

onload = () => {
    const tabCounterSwitcher = document.getElementById('toggle-tabCounter')
    if (tabCounterSwitcher) {
        tabCounterSwitcher.addEventListener('change', function(event) {
            if (event.target.checked) {
                document.cookie = 'tabCounter=true'
            } else {
                document.cookie = 'tabCounter=false'
            }
        });
    }
}
