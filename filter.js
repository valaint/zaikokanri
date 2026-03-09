function filterTable()
{
    // Variables
    let dropdown, searchInput, table, rows, cells, categoryCell, nameCell, categoryFilter, textFilter;
    dropdown = document.getElementById("categorylist");
    searchInput = document.getElementById("searchArticleList");
    table = document.getElementById("inventorylist");
    rows = table.getElementsByTagName("tr");

    categoryFilter = dropdown ? dropdown.value : "All";
    textFilter = searchInput ? searchInput.value.toLowerCase() : "";

    // Loops through rows and hides those that don't match the filters
    for (let i = 1; i < rows.length; i++) { // Start at 1 to skip header row
        let row = rows[i];
        cells = row.getElementsByTagName("td");
        categoryCell = cells[0] || null;
        nameCell = cells[1] || null;

        if (!categoryCell || !nameCell) {
            continue;
        }

        let categoryMatch = (categoryFilter === "All" || categoryFilter === categoryCell.textContent.trim());
        let textMatch = (textFilter === "" || nameCell.textContent.toLowerCase().includes(textFilter));

        if (categoryMatch && textMatch) {
            row.style.display = ""; // shows this row
        } else {
            row.style.display = "none"; // hides this row
        }
    }
}