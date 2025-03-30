function getCookieValue(name) {
    const cookieValue = document.cookie
        .split("; ")
        .find((row) => row.startsWith(name + '='));

    // Ensure cookieValue exists before accessing it
    if (cookieValue) {
        const value = cookieValue.split("=")[1];
        if (!value) {
            return null;
        }
        return value;
    } else {
        return null;
    }
}