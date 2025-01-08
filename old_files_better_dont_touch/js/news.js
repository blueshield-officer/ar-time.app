function add0toLess10(input){
    if (input < 10){
        return '0' + input
    }
    else {
        return input
    }
}


if (news){
    news.forEach((element) => {
        // main news box
        const news_card = document.createElement("div");
        news_card.className = "news-card box-shadow background-gradient";

        // image
        let image;
        if (element.image === null){
            image = document.createElement("div");
            console.log('no image')
        }
        else {
            image = document.createElement("img");
            image.src = '/img/news/' + element['image'] + '.png';
            image.alt = 'News Image'
        }

        image.className = 'image'
        news_card.appendChild(image)

        // Text box
        const body = document.createElement("div");
        body.className = 'news-body'
        news_card.appendChild(body)

        // Title
        const title = document.createElement("p");
        title.innerHTML = element.title
        title.className = 'title'
        body.appendChild(title)

        // Content
        const content = document.createElement("p");
        content.innerHTML = element.content
        content.className = 'content'
        body.appendChild(content)

        // publication date box
        const dateBox = document.createElement("div");
        dateBox.className = 'date-box'
        body.appendChild(dateBox)

        // time and date
        const dateObject = new Date(element.date)
        const formatter = new Intl.DateTimeFormat('en-US', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const timestamp = (formatter.format(dateObject)).replace(/\//g, '.');
        let minutes = dateObject.getMinutes()
        minutes = add0toLess10(minutes)
        let hours = dateObject.getHours();
        let dayPart
        if (hours === 0){
            hours = 12
            dayPart = 'PM'
        }
        else if (hours <= 12){
            if (hours < 10){
                hours =  '0' + hours
            }
            dayPart = 'AM'
        }
        else if (hours > 12){
            hours = (hours - 12);
            if (hours < 10){
                hours = '0' + hours;
            }
            dayPart = 'PM'
        }


        const time = document.createElement("p");
        time.className = 'publication-time'
        time.innerText = hours + ':' + minutes + ' ' + dayPart
        dateBox.appendChild(time)

        const date = document.createElement("p");
        date.className = 'publication-date'
        date.innerText = timestamp
        dateBox.appendChild(date)


        // append whole element to block
        document.getElementById('news-box').appendChild(news_card)
    })
}