class AbsentsData {
  constructor() {
    this.data = absence;
    this.version_time = absence.version_time;
    this.md5 = absence.md5

    this.temp = null;
  }

  async updateCheck() {
    try {
      const response = await fetch('https://ar-time.loc/database/sql_output/absenceJSON.php');
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      const data = await response.json();
      this.tempData = data
    } catch (error) {
      console.error(error);
    }
    if (this.md5 === this.tempData.doc_md5){
      return false
    }
    else {
      this.data = JSON.parse(this.tempData.doc_json)
      this.md5 = this.tempData.md5
      this.version_time = this.tempData.version_time
      return true
    }
  }

  createMissingTable(type){
    const element = document.createElement("div");
    element.id = type + '-block';
  }
  fillData(type){

  }
}
















const myAbsents = new AbsentsData()

async function updater (){
  const newData = await myAbsents.updateCheck()
  console.log(newData)
}
