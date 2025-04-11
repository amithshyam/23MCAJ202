<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "tkm");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$insert_msg = $search_msg = "";
$results = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $book_id = $_POST["book_id"];
    $title = $_POST["title"];
    $authors = $_POST["authors"];
    $edition = $_POST["edition"];
    $publisher = $_POST["publisher"];

    $stmt = $conn->prepare("INSERT INTO book (book_id, title, authors, edition, publisher) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $book_id, $title, $authors, $edition, $publisher);

    if ($stmt->execute()) {
        $insert_msg = "Book added successfully!";
    } else {
        $insert_msg = "Error: " . $conn->error;
    }
    $stmt->close();
}

// Handle search
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"])) {
    $search_title = $_POST["search_title"];
    $stmt = $conn->prepare("SELECT * FROM book WHERE title LIKE ?");
    $like = "%" . $search_title . "%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $results = $stmt->get_result();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Info Manager</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; padding: 20px; }
        .form-section, .search-section { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background-color:rgb(55, 18, 18); color: white; }
        .message { color: green; font-weight: bold; }
    </style>
</head>
<body>

<div class="form-section">
    <h2>Add Book</h2>
    <form method="POST">
        <label>book_id:</label><br>
        <input type="number" name="book_id" required><br>
        <label>Title:</label><br>
        <input type="text" name="title" required><br>
        <label>Authors:</label><br>
        <input type="text" name="authors" required><br>
        <label>Edition:</label><br>
        <input type="text" name="edition" required><br>
        <label>Publisher:</label><br>
        <input type="text" name="publisher" required><br><br>
        <button type="submit" name="add">Add Book</button>
    </form>
    <p class="message"><?php echo $insert_msg; ?></p>
</div>

<div class="search-section">
    <h2>Search Book by Title</h2>
    <form method="POST">
        <input type="text" name="search_title" required>
        <button type="submit" name="search">Search</button>
    </form>

    <?php if (!empty($results) && $results->num_rows > 0): ?>
        <h3>Search Results:</h3>
        <table>
            <tr>
                <th>Book Id</th>
                <th>Title</th>
                <th>Authors</th>
                <th>Edition</th>
                <th>Publisher</th>
            </tr>
            <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['book_id']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $row['authors']; ?></td>
                    <td><?php echo $row['edition']; ?></td>
                    <td><?php echo $row['publisher']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif (isset($_POST["search"])): ?>
        <p>No results found for "<?php echo htmlspecialchars($_POST["search_title"]); ?>"</p>
    <?php endif; ?>
</div>

</body>
</html>
