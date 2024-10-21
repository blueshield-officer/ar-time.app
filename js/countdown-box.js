let options = {
    timeZone: 'America/New_York',
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    second: 'numeric',
  },
  formatter = new Intl.DateTimeFormat([], options);


console.log(data)

class ManagerUNIX {
  constructor(data) {
    this.date = data.date
    this.accelerator = 0
  }
  particleAccelerator() {
    this.accelerator += 1
  }
  day(){
    let tempDate = this.date.split('/')
    let date = new Date(tempDate[2], tempDate[0]-1, tempDate[1])
    date = new Date(date.getFullYear(), date.getMonth(), date.getDate())
    return Math.floor(date.valueOf()/1000)
  }
  now(){
    return Math.floor(Date.now()/1000) + this.accelerator
  }
  secondToTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    return {
      hours,
      minutes,
      seconds: remainingSeconds
    };
  }
}

class ClassStatus {
  constructor(data, position, period, status, unix) {
    this.bells = data.bells;
    this.position = position;
    this.period = period;
    this.status = status;
    this.UNIX = unix;
    if (this.status === 0) {
      this.lastUNIX = null
      this.nextUNIX = this.bells[position].start_unix
      this.statusText = 'starts in'
    }
    else {
      this.lastUNIX = this.bells[position].start_unix
      this.nextUNIX = this.bells[position].end_unix
      this.statusText = 'ends in'
    }
    this.period = position === 1 && period === 'homeroom' ? 'school' : this.period;
    this.lastUNIX = this.lastUNIX = null ? null : this.lastUNIX+=this.UNIX;
    this.nextUNIX += this.UNIX

    this.countdownTime = document.getElementById('countdown-time');
    this.countdownLabel = document.getElementById('countdown-label');
    if (status === 1){
      this.progressBar()
      document.getElementById('countdown-progress-time').innerText = this.bells[this.position].end_time
    }

    // important function loaders
    this.scheduleTable()
    this.labelUpdate()
    this.boxInfo()
  }
  console(){
    return {
      position: this.position,
      period: this.period,
      status: this.status,
      statusText: this.statusText,
      nextUNIX: this.nextUNIX,
      lastUNIX: this.lastUNIX,
      UNIX: this.UNIX,
    }
  }

  progressBar() {
    if (this.status === 1) {
      let element = document.createElement('div');
      element.id = 'countdown-progress-box'
      document.getElementById('countdown').appendChild(element);
      //
      element = document.createElement('div');
      element.id = 'countdown-progress-info'
      document.getElementById('countdown-progress-box').appendChild(element);
      //
      element = document.createElement('span');
      element.id = 'countdown-progress-percentage'
      document.getElementById('countdown-progress-info').appendChild(element);
      //
      element = document.createElement('span');
      element.id = 'countdown-progress-time'
      document.getElementById('countdown-progress-info').appendChild(element);
      //
      element = document.createElement('div');
      element.id = 'countdown-progress-bar'
      document.getElementById('countdown-progress-box').appendChild(element);
      //
      element = document.createElement('span');
      element.id = 'countdown-progress-bar-filled'
      element.className = 'percentage-color'
      document.getElementById('countdown-progress-bar').appendChild(element);
      //
    }
    if (this.status === 0) {
      document.getElementById('countdown-progress-box').remove();
    }
  }
  labelUpdate(){
    this.period = this.period.charAt(0).toUpperCase() + this.period.slice(1)
    this.countdownLabel.innerHTML = '<strong>' + this.period + '</strong>' + ' ' + this.statusText + ':'
  }

  progressBarUpdate (percentage) {
    document.getElementById('countdown-progress-bar-filled').style.backgroundColor = 'hsl(' + 0.9 * percentage + ', 100%, 50%)'
  }

  boxInfo () {
    // date box
    const date = new Date(data.date)
    const dateBoxID = document.getElementById('date-box')
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const day = date.getDate();
    const monthName = monthNames[date.getMonth()];
    const weekName = dayNames[date.getDay()];
    const year = date.getFullYear();

    dateBoxID.innerText = weekName + ', ' + day + ' ' + monthName + ', ' + year;


    // schedule box
    const scheduleBoxID = document.getElementById('schedule-box')
    let schedule
    const numericSchedule = parseInt(data.schedule)
    if (Number.isInteger(numericSchedule)) {
      const scheduleName = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'P', 'R', 'T', 'V'];
      schedule = scheduleName[numericSchedule - 1];
      if (schedule === 'P') {
        schedule = 'P (Pep Rally)'
      }
    }
    scheduleBoxID.innerHTML = '<strong>' + schedule + '</strong>' + ' ' + 'Schedule' + '<br>' + data.testDay + ' Day'
  }

  scheduleTable () {
    /*
    const position = this.position - 1
    const status = this.status
    console.log(position, status)
    if (status === 0 && position === 0) {

    } else {
      document.addEventListener('DOMContentLoaded', function () {
        const tableRows = document.getElementsByClassName('table-row');
        for (let i = 0; i <= position; i++) {
          tableRows[i].style.backgroundColor = 'green';
        }
        if (status === 0) {
          tableRows[this.period - 1].style.backgroundColor = 'yellow';
        } else {
          tableRows[this.period - 1].style.backgroundColor = 'red'
        }
      })
    }
     */
  }


  nextPeriod() {
    //  0 => 1, period started
    if (this.status === 0) {
      // check on -
      if (this.bells[this.position].end_unix === null) {
        location.reload()
      }
      this.lastUNIX = this.bells[this.position].start_unix
      this.nextUNIX = this.bells[this.position].end_unix
      this.status = 1
      this.statusText = 'ends in'
      // check in order to skipp announcements
      let tempPosition = this.position + 1
      //creating progress
      this.progressBar()
      if (typeof this.bells[tempPosition] !== 'undefined' && this.bells[tempPosition].name === 'announcements') {
        this.nextUNIX = this.bells[tempPosition].end_unix
        document.getElementById('countdown-progress-time').innerText = this.bells[tempPosition].end_time
      } else {
        document.getElementById('countdown-progress-time').innerText = this.bells[this.position].end_time
        document.getElementById('countdown-progress-time').innerText = this.bells[this.position].end_time
      }
    }
    // 1 => 0, period ended
    else {
      this.position += 1
      console.log(typeof this.bells[this.position])
      if (typeof this.bells[this.position] === 'undefined') {
        location.reload()
      }
      if (this.bells[this.position].name === 'announcements') {
        this.position += 1
      }
      this.status = 0
      this.statusText = 'starts in'
      this.lastUNIX = null
      this.nextUNIX = this.bells[this.position].start_unix

      //deleting progress
      this.progressBar()
    }
    this.nextUNIX += this.UNIX
    this.lastUNIX = this.lastUNIX = null ? null : this.lastUNIX += this.UNIX;
    this.period = this.bells[this.position].name
    this.labelUpdate()
    counterUpdater(UNIX, classStatus)
  }
}


function counterUpdater(UNIX, classStatus) {
  let totalSeconds = classStatus.nextUNIX - UNIX.now();
  // progress update if status === 1
  if (classStatus.status === 1) {
    let progressBar = document.getElementById('countdown-progress-bar-filled');
    let progressPercentage = document.getElementById('countdown-progress-percentage');
    let percentage = 0
    if (totalSeconds <= 0) {
      percentage = 100
    } else if (totalSeconds > classStatus.nextUNIX - classStatus.lastUNIX) {
      percentage = 0
    } else {
      percentage = 100 - (totalSeconds / (classStatus.nextUNIX - classStatus.lastUNIX) * 100)
    }
    progressBar.style.width = percentage + '%'
    progressPercentage.innerText = Math.floor(percentage) + '%'
    classStatus.progressBarUpdate(percentage)
  }


  if (totalSeconds < 0) {
    classStatus.nextPeriod();
    totalSeconds = classStatus.nextUNIX - UNIX.now()
  }
  const convertedTime = UNIX.secondToTime(totalSeconds);
  let myArray = []
  if (convertedTime.hours > 0) {
    if (convertedTime.hours < 10) {
      convertedTime.hours = '0' + convertedTime.hours
    }
    myArray.push(convertedTime.hours)
  }
  if (convertedTime.minutes > 0 || convertedTime.hours > 0) {
    if (convertedTime.minutes < 10) {
      convertedTime.minutes = '0' + convertedTime.minutes
    }
    myArray.push(convertedTime.minutes)
  }
  if (convertedTime.seconds < 10) {
    convertedTime.seconds = '0' + convertedTime.seconds
  }
  myArray.push(convertedTime.seconds)
  classStatus.countdownTime.innerHTML = myArray.join(':')
}



let UNIX = new ManagerUNIX(data)
let classStatus
window.onload = function () {
  console.log(data)
  data.bells.every(function (element) {
    if (element.name === 'warning bell' && element.position === 1) {
      return true
    } else {
      if (UNIX.now() < UNIX.day() + element.start_unix) {
        //period starts in
        classStatus = new ClassStatus(data, element.position - 1, element.name, 0, UNIX.day())
        return false
      } else if (UNIX.now() < UNIX.day() + element.end_unix) {
        //period ends in
        classStatus = new ClassStatus(data, element.position - 1, element.name, 1, UNIX.day())
        return false
      }
      return true
    }
  })
  counterUpdater(UNIX, classStatus)
  setInterval('counterUpdater(UNIX, classStatus)', 1000)
}

//classStatus.nextPeriod()

// particle accelerator
//setInterval('counterUpdater(UNIX, classStatus)', 1)
//setInterval('UNIX.particleAccelerator()', 1)
UNIX.accelerator = 57600