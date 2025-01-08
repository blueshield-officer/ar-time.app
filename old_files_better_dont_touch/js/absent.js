class DataUpdate {

    constructor() {
        this.data = absence.data
        this.md5 = absence.md5
        this.version_time = absence.version_time
        this.tempData = null
    }

    async checkForDataUpdate() {
        try {
            const response = await fetch('/database/sql_output/absenceJSON.php');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            this.tempData = data
        } catch (error) {
            console.error(error);
        }
        if (this.md5 === this.tempData.doc_md5) {
            return false
        } else {
            this.data = JSON.parse(this.tempData.doc_json)
            this.md5 = this.tempData.doc_md5
            this.version_time = this.tempData.doc_time_version
            return true
        }
    }

    absentFill(job) {
        // DATA UPDATE FOR ABSENT STAFF/TEACHERS
        const dataPath = this.data[job];
        dataPath.forEach(object => {
            let element
            element = document.createElement('p');
            element.innerText = object;
            element.className = job + '-name name pointable'
            document.getElementById(job + '-list').appendChild(element)
        })
    }

    absentCreate(job) {
        // CREATES ABSENT TEACHERS/STUFF BLOCK IF IT WAS DELETED
        let element
        element = document.createElement('div')
        element.id = job + '-block'
        element.className = 'absent-' + job + ' block absent-list'
        document.getElementById('info-block').appendChild(element)

        element = document.createElement('p');
        element.className = 'title'
        element.innerText = 'Absent ' + job + ' today:'
        document.getElementById(job + '-block').appendChild(element)

        element = document.createElement('div');
        element.className = 'absent-' + job + '-list'
        element.id = job + '-list'
        document.getElementById(job + '-block').appendChild(element)
    }

    roomChangesCreate() {
        // creating room changes field if it was deleted
        let element
        element = document.createElement('div')
        element.id = 'roomChanges-block'
        element.className = 'room-changes block'
        document.getElementById('info-block').appendChild(element)

        element = document.createElement('p');
        element.className = 'title'
        element.innerText = 'Room Changes:'
        document.getElementById('roomChanges-block').appendChild(element)

        element = document.createElement('div');
        element.id = 'room-changes-list'
        element.className = 'list'
        document.getElementById('roomChanges-block').appendChild(element)
    }

    roomChangesFill() {
        // INSERTING ROOM CHANGES DATA
        const dataPath = this.data['roomChanges']
        let i
        let object
        for (i = 1; i <= 8; i++) {
            object = dataPath[i]
            if (object !== undefined) {
                let element
                element = document.createElement('div')
                element.id = 'period-' + i
                element.className = 'period-box pointable'
                document.getElementById('room-changes-list').appendChild(element)

                element = document.createElement('p')
                element.className = 'period-number'
                element.innerText = 'Period ' + i + ' '
                document.getElementById('period-' + i).appendChild(element)

                element = document.createElement('div')
                element.className = 'directions-box'
                element.id = 'directions-box-' + i
                document.getElementById('period-' + i).appendChild(element)

                object.forEach(function (direction) {
                    element = document.createElement('p')
                    element.className = 'direction'
                    element.innerHTML = direction
                    document.getElementById('directions-box-' + i).appendChild(element)
                })
            }
        }

    }

    removeEmptyFields() {
        // REMOVING EMPTY TABLES
        // must be last

        const myArray = ['teachers-name', 'staff-name', 'period-box']
        myArray.forEach(element => {
            const object = document.getElementsByClassName(element).length
            if (object === 0) {
                let blockRemove
                switch (element) {
                    case 'teachers-name':
                        blockRemove = document.getElementById('teachers-block')
                        break
                    case 'staff-name':
                        blockRemove = document.getElementById('staff-block')
                        break
                    case 'period-box':
                        blockRemove = document.getElementById('roomChanges-block')
                        break
                }
                if (blockRemove !== null) {
                    blockRemove.remove()
                }
            }
        })
        const noDataElement = document.getElementById('no-data-block')
        if (noDataElement !== null) {
            noDataElement.remove()
        }
    }

    removeOldData() {
        // REMOVING ALL OLD DATA

        ['period-box', 'staff-name', 'teachers-name'].forEach(element => {
            const arrayToRemove = document.getElementsByClassName(element)
            if (arrayToRemove !== null) {
                Array.from(arrayToRemove).forEach(object => {
                    object.remove()
                })
            }
        })
    }

    noData() {
        // CREATING NO DATA BOX
        let element
        element = document.createElement('div')
        element.id = 'no-data-block'
        element.className = 'no-data block'
        document.getElementById('info-block').appendChild(element)

        element = document.createElement('p');
        element.innerText = 'No Data'
        element.className = 'title no-data'
        document.getElementById('no-data-block').appendChild(element)
    }

    timeVersionUpdate() {
        let element
        element = document.getElementById('update-time')
        element.innerText = 'Version time: ' + this.version_time
    }
}


const myAbsence = new DataUpdate()



//first data load
if (myAbsence.data !== null) {
    myAbsence.absentFill('teachers')
    myAbsence.absentFill('staff')
    myAbsence.roomChangesFill()
    myAbsence.removeEmptyFields()

    myAbsence.timeVersionUpdate()
} else {
    myAbsence.removeOldData()
    myAbsence.removeEmptyFields()

    myAbsence.noData()
    myAbsence.timeVersionUpdate()
}


async function updateIfNeeded() {
    const dataUpdated = await myAbsence.checkForDataUpdate();

    if (dataUpdated) {
        console.log('yes update')
        if (myAbsence.data !== null) {
            // cleaning old boxes data
            myAbsence.removeOldData()
            {
                ['teachers', 'staff', 'roomChanges'].forEach(function (element) {
                    console.log(element)
                    if (myAbsence.data[element].length > 0 || Object.keys(element).length) {
                        console.log(element + 'passed')
                        // CREATING BLOCK IF IT IS MISSING
                        let block = document.getElementById(element + '-block')
                        if (block === null) {
                            switch (element) {
                                case 'roomChanges':
                                    myAbsence.roomChangesCreate()
                                    break
                                default:
                                    myAbsence.absentCreate(element)
                            }
                        }

                        //FILLING BLOCK WITH INFORMATION
                        switch (element) {
                            case 'roomChanges':
                                myAbsence.roomChangesFill()
                                break
                            default:
                                myAbsence.absentFill(element)
                        }
                    }
                })
            }
            //cleaning tables which are empty
            myAbsence.removeEmptyFields()
        } else {
            myAbsence.removeOldData()
            myAbsence.removeEmptyFields()

            myAbsence.noData()
        }
        myAbsence.timeVersionUpdate()
    } else {
        console.log('no update')
    }
}

setInterval('updateIfNeeded()', 10000)