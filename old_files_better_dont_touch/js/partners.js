console.log(partners)
if (partners){
    partners.forEach((element) => {
        // holder-border
        const border = document.createElement("div");
        border.className = 'partner-border rank-' + element['rank'];

        // account
        const account = document.createElement('div');
        account.className = 'account-box';
        border.appendChild(account)

        // avatar
        const avatar = document.createElement('img');
        avatar.src = '/img/partners/' + element['account'] + '.png';
        avatar.alt = 'avatar'
        avatar.className = 'avatar'
        account.appendChild(avatar);

        // name
        const name = document.createElement('p');
        name.innerText = element['account'];
        name.className = 'name'
        account.appendChild(name);

        //  link ('a')
        const link = document.createElement('a');
        link.className = 'link'
        link.href = element['link'];
        link.target = '_blank';
        account.appendChild(link);

        // button itself
        const follow = document.createElement('p');
        follow.className = 'follow'
        follow.innerText = 'Follow';
        link.appendChild(follow);

        // last one
        document.getElementById('partners-list').appendChild(border)
    })
}