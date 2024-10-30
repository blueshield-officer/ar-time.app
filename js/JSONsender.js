function reqListener() {
    console.log(this.responseText);
}
const req = new XMLHttpRequest();
req.addEventListener("load", reqListener);
req.open("GET", "https://ar-time.loc/database/sql_output/absence.php");
req.send();
