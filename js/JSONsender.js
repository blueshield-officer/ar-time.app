function reqListener() {
    console.log(this.responseText);
}

const req = new XMLHttpRequest();
req.addEventListener("load", reqListener);
req.open("GET", "https://ar-time.loc/dataLoader.php");
req.send();