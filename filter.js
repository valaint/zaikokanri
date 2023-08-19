function filterTable() {
    // Variables
    let dropdown, table, rows, cells, category, filter;
    dropdown = document.getElementById("categorylist");
    table = document.getElementById("inventorylist");
    rows = table.getElementsByTagName("tr");
    filter = dropdown.value;

    // Loops through rows and hides those with countries that don't match the filter
    for (let row of rows) { // `for...of` loops through the NodeList
        cells = row.getElementsByTagName("td");
        category = cells[0] || null; // gets the 2nd `td` or nothing
        // if the filter is set to 'All', or this is the header row, or 2nd `td` text matches filter
        if (filter === "All" || !category || (filter === category.textContent)) {
            row.style.display = ""; // shows this row
        }
        else {
            row.style.display = "none"; // hides this row
        }
    }
}