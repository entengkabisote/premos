<?php
include 'db_connect.php';
$query = "SELECT equipment_name_id, equipment_name, category_name FROM equipment_name INNER JOIN equipment_category ON equipment_name.equipment_category_id = equipment_category.equipment_category_id ORDER BY equipment_name_id DESC";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . htmlspecialchars($row['equipment_name']) . "</td>
            <td>" . htmlspecialchars($row['category_name']) . "</td>
            <td>
                <a href='edit_equipment_rh.php?id={$row['equipment_name_id']}' class='btn btn-sm btn-outline-primary'><i class='fa fa-edit'></i></a>
                <button type='button' class='btn btn-sm btn-outline-danger delete-machinery' data-id='{$row['equipment_name_id']}'><i class='fa fa-trash'></i></button>
            </td>
        </tr>";
    }
}
?>
