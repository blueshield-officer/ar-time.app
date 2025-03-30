addEventListener("DOMContentLoaded", (event) => {
    // const sideBarStatus = getCookieValue('sideBar')
    // console.log(sideBarStatus);
    // toggleSideBar(sideBarStatus, false)
    const account_box_element = document.getElementById('account-box')
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
    console.log(status);
    document.cookie = "sideBar=";
    document.cookie = "sideBar=" + status;

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
        element.style.transitionDuration = duration
    }
    function changeStatus(object, className, status) {
        if (status === 0) {
            object.classList.remove('r-' + className)
            object.classList.add('s-' + className)
        }
        else {
            object.classList.remove('s-' + className)
            object.classList.add('r-' + className)
        }
    }

    const sideBarObject = document.getElementById('side-bar');
    const topBarObject = document.getElementById('top-bar');
    const collapse = document.getElementById('collapse-button');
    const collapseIcon = collapse.getElementsByClassName('icon')[0]

    const websiteLogo = document.getElementById('website-logo')
    const websiteLogoDivider = document.getElementById('website-logo-divider')
    const websiteLogoDividerLine = document.getElementById('website-logo-divider-line')
    const websiteTitle = document.getElementById('website-title')

    const content = document.getElementById('content');

    // if (animation) {
    //     transitionTo(topBarObject, transition.time)
    //     transitionTo(sideBarObject, transition.time)
    //     transitionTo(collapse, transition.time)
    //     transitionTo(collapseIcon, transition.time)
    //     transitionTo(collapseP, transition.time)
    //
    //     transitionTo(websiteLogo, transition.time)
    //     transitionTo(websiteLogoDivider, transition.time)
    //     transitionTo(websiteLogoDividerLine, transition.time)
    //     transitionTo(websiteTitle, transition.time)
    //
    //     transitionTo(content, transition.time)
    // }

    // const pagesTitlesObjects = sideBarObject.getElementsByClassName('pageTitle')

    changeStatus(sideBarObject, 'side-bar', status)
    changeStatus(topBarObject, 'top-bar', status)
    changeStatus(collapse, 'collapse-button', status)
    changeStatus(collapseIcon, 'icon', status)
    changeStatus(websiteLogo, 'icon-holder', status)
    changeStatus(websiteLogoDivider, 'website-divider-holder', status)
    changeStatus(websiteLogoDividerLine, 'website-divider', status)
    changeStatus(websiteTitle, 'logoTitle', status)
    changeStatus(content, 'content', status)


    if (status === 0) {
        collapse.onclick = () => {
            toggleSideBar(1)
        }
    } else {
        collapse.onclick = () => {
            toggleSideBar(0)
        }
    }



    // await removeTimeOut(sideBarObject, transition.milliseconds);
    // await removeTimeOut(collapseIcon, transition.milliseconds);
    // await removeTimeOut(collapseP, transition.milliseconds);
    // await removeTimeOut(collapse, transition.milliseconds);
    // await removeTimeOut(content, transition.milliseconds);
}

