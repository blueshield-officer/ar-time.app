addEventListener("DOMContentLoaded", (event) => {
    const account_box_element = document.getElementById('account-box')
    if (account_box_element !== null) {

    }
});

function toggleThemes(){
    let theme = document.getElementById('theme');
    const currentTheme = theme.getAttribute('href')
    if (currentTheme.includes('light')) {
        theme.setAttribute('href', currentTheme.replace('light', 'dark'));
    }
    else {
        theme.setAttribute('href', currentTheme.replace('dark', 'light'));
    }
}

async function toggleSideBar(status) {
    const transition = {
        time: '0.3s',
        milliseconds: 300
    }

    async function removeTimeOut(element, timeout) {
        setTimeout(() => {
            element.style.transitionDuration = 'none'
        }, timeout)
    }

    function transitionTo(element, duration) {
        console.log(duration)
        element.style.transitionDuration = duration
    }

    const sideBarObject = document.getElementById('side-bar');
    const collapse = document.getElementById('collapse');
    const collapseIcon = collapse.getElementsByClassName('icon')[0]
    const collapseP = collapse.getElementsByTagName('p')[0]

    const websiteLogoSection = document.getElementById('website-logo-section');
    const websiteLogo = document.getElementById('website-logo')
    const websiteLogoDivider = document.getElementById('website-logo-divider')
    const websiteTitle = document.getElementById('website-title')

    const screen = document.getElementById('screen');

    transitionTo(sideBarObject, transition.time)
    transitionTo(collapse, transition.time)
    transitionTo(collapseIcon, transition.time)
    transitionTo(collapseP, transition.time)

    transitionTo(websiteLogoSection, transition.time)
    transitionTo(websiteLogo, transition.time)
    transitionTo(websiteLogoDivider, transition.time)
    transitionTo(websiteTitle, transition.time)

    transitionTo(screen, transition.time)

    const pagesTitlesObjects = sideBarObject.getElementsByClassName('pageTitle')


    if (status === 0) {
        // left-bar
        sideBarObject.style.width = '210px';
        collapseP.style.marginLeft = '10px'
        collapseIcon.style.rotate = '0deg';
        for (const element of Array.from(pagesTitlesObjects)) {
            element.style.transition = transition.time
            element.style.marginLeft = '10px';
            await removeTimeOut(element, transition.milliseconds);
        }
        collapse.onclick = () => {
            toggleSideBar(1)
        }

        // logo
        websiteLogoSection.style.width = '210px';
        websiteTitle.style.marginLeft = '0px'
        websiteLogoDivider.style.opacity = '1'
        websiteLogoDivider.style.height = '60px'
        websiteLogo.style.borderRadius = '10px 0 0 10px';
        websiteTitle.style.borderRadius = '0px 10px 10px 0px'

        //screen
        screen.style.left = '210px'
        screen.style.width = 'calc(100% - 210px)'

    } else {
        // left-bar
        sideBarObject.style.width = '75px';
        collapseP.style.marginLeft = '40px'
        collapseIcon.style.rotate = '180deg';
        for (const element of Array.from(pagesTitlesObjects)) {
            element.style.transition = transition.time
            element.style.marginLeft = '40px';
            await removeTimeOut(element, transition.milliseconds);
        }
        collapse.onclick = () => {
            toggleSideBar(0)
        }

        // logo
        websiteLogoSection.style.width = '75px';
        websiteTitle.style.marginLeft = '40px'
        websiteLogoDivider.style.opacity = '0'
        websiteLogoDivider.style.height = '0px'
        // websiteTitle.style.textWrap = 'nowrap';
        websiteLogo.style.borderRadius = '10px';
        websiteTitle.style.borderRadius = '10px'

        // screen
        screen.style.left = '75px'
        screen.style.width = 'calc(100% - 75px)'
    }

    // await removeTimeOut(sideBarObject, transition.milliseconds);
    // await removeTimeOut(collapseIcon, transition.milliseconds);
    // await removeTimeOut(collapseP, transition.milliseconds);
    // await removeTimeOut(collapse, transition.milliseconds);
    // await removeTimeOut(screen, transition.milliseconds);
}