<section class="home-section">
<div class="text">Admin Panel</div>
<div class="teacher-container">
    <!-- HEADER CONTROLS -->
    <div class="table-header">
    <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchBar" placeholder="Search..." onkeyup="searchCards()">
        </div>
        <div>
            <button class="download-btn">Newest ⬇</button>
            <button class="download-btn" style="background-color: #ffbe00; color: black;">+ New Student</button>
        </div>
    </div>

    <!-- Student TABLE -->
   <div class="table-container">
   <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Downloadable Files</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>T10001</td>
                <td>Marcus Levin</td>
                <td>
                    <button class="download-btn pdf-btn">PDF</button>
                    <button class="download-btn csv-btn">CSV</button>
                </td>
                <td class="action-icons">
                    <i class="fa fa-pencil"></i>
                    <i class="fa fa-trash"></i>
                </td>
            </tr>

            <tr>
                <td>T10001</td>
                <td>Marcus Levin</td>
                <td>
                    <button class="download-btn pdf-btn">PDF</button>
                    <button class="download-btn csv-btn">CSV</button>
                </td>
                <td class="action-icons">
                    <i class="fa fa-pencil"></i>
                    <i class="fa fa-trash"></i>
                </td>
            </tr>
            <tr>
                <td>T10001</td>
                <td>Marcus Levin</td>
                <td>
                    <button class="download-btn pdf-btn">PDF</button>
                    <button class="download-btn csv-btn">CSV</button>
                </td>
                <td class="action-icons">
                    <i class="fa fa-pencil"></i>
                    <i class="fa fa-trash"></i>
                </td>
            </tr>
            <tr>
                <td>T10002</td>
                <td>Tatiana Donin</td>
                <td>
                    <button class="download-btn pdf-btn">PDF</button>
                    <button class="download-btn csv-btn">CSV</button>
                </td>
                <td class="action-icons">
                    <i class="fa fa-pencil"></i>
                    <i class="fa fa-trash"></i>
                </td>
            </tr>
            <tr>
                <td>T10003</td>
                <td>Tiana Dorwart</td>
                <td>
                    <button class="download-btn pdf-btn">PDF</button>
                    <button class="download-btn csv-btn">CSV</button>
                </td>
                <td class="action-icons">
                    <i class="fa fa-pencil"></i>
                    <i class="fa fa-trash"></i>
                </td>
            </tr>
        </tbody>
    </table>
   </div>

    <!-- PAGINATION -->
    <div class="pagination">
        <a href="#">1</a>
        <a href="#">2</a>
        <a href="#">3</a>
        <a href="#">4</a>
        <a href="#">...</a>
        <a href="#">12</a>
    </div>
</div>

</section>
