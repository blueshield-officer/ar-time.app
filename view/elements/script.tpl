<script>
    function analyzeSQLdata(str) {
        if (str.length === 0){
            return null
        }
        else {
            try {
                const result = JSON.parse(str);
                return result;
            } catch (e) {
                return str;
            }
        }
    }
    async function getData(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Response status:' + response.status);
            }

            const json = await response.json();
            console.log(json);
        } catch (error) {
            console.error(error.message);
        }
    }

    const currentDate = new Date()
    const date = '{$localDate}';
    const currentScheduleTable = analyzeSQLdata('{$currentTable}')
</script>