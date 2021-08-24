<?php
include('phpqrcode/qrlib.php');
include('config.php');
session_start();


$received_data = json_decode(file_get_contents("php://input"));
$data = array();

if ($received_data->action == 'fetchall') {
    $currentPage = $received_data->currentPage;
    $start_of = $received_data->offset * ($currentPage - 1);

    $query =  " SELECT i.Items_ID, o.owner_post, o.owner_name, o.owner_division, i.Type, i.Model, i.Serial, i.QR_Code, i.File_reference, i.Maintaince FROM inventory i , owners o
    WHERE i.owner_id = o.owner_id ";

    if ($received_data->Search_division != '') {
        $query = $query . " and o.owner_division like '%" . $received_data->Search_division . "%'";
    }

    if ($received_data->Search_post != '') {
        $query = $query . " and o.owner_post like '%" . $received_data->Search_post . "%'";
    }

    if ($received_data->Search_name != '') {
        $query = $query . " and o.owner_name like '%" . $received_data->Search_name . "%'";
    }

    if ($received_data->Search_Type != '') {
        $query = $query . " and i.Type like '%" . $received_data->Search_Type . "%'";
    }

    if ($received_data->Search_Model != '') {
        $query = $query . " and i.Model like '%" . $received_data->Search_Model . "%'";
    }

    if ($received_data->Search_Serial != '') {
        $query = $query . " and i.Serial like '%" . $received_data->Search_Serial . "%'";
    }

    $query = $query . " Limit ?,?";

    $statement = $conn->prepare($query);
    $statement->bindParam(1, $start_of, PDO::PARAM_INT);
    $statement->bindParam(2, $received_data->offset, PDO::PARAM_INT);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        //echo json_encode($row);
        array_push($data, array(
            'Items_ID' => $row['Items_ID'],
            'owner_post' => $row['owner_post'],
            'owner_name' => $row['owner_name'],
            'owner_division' => $row['owner_division'],
            'Type' => $row['Type'],
            'Model' => $row['Model'],
            'Serial' => $row['Serial'],
            'QR_Code' => base64_encode($row['QR_Code']),
            'File_reference' => $row['File_reference'],
            'Maintaince' => $row['Maintaince']
        ));
    }

    echo json_encode($data);
} else if ($received_data->action == 'fetchSingle') {
    $query = " SELECT i.Items_ID, o.owner_post, o.owner_name, o.owner_division, i.Type, i.Model, i.Serial, i.File_reference, i.Maintaince FROM inventory i , owners o WHERE o.owner_id = i.owner_id and i.Items_id = ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->id);
    $statement->execute();
    $result = $statement->fetchAll();

    foreach ($result as $row) {
        $data['Items_ID'] = $row['Items_ID'];
        $data['owner_post'] = $row['owner_post'];
        $data['owner_name'] = $row['owner_name'];
        $data['owner_division'] = $row['owner_division'];
        $data['Type'] = $row['Type'];
        $data['Model'] = $row['Model'];
        $data['Serial'] = $row['Serial'];
        $data['File_reference'] = $row['File_reference'];
        $data['Maintaince'] = $row['Maintaince'];
    }

    echo json_encode($data);
} else if ($received_data->action == 'insert') {
    $data = array(
        ':owner_id' => $received_data->owner_ID,
        ':Type' => $received_data->Type,
        ':Model' => $received_data->Model,
        ':Serial' => $received_data->Serial,
        ':FileRef' => $received_data->FileRef,
        ':maintenance' => $received_data->maintenance
    );

    $query = "INSERT into inventory values (null, :owner_id,:Type,:Model,:Serial,:FileRef, :maintenance ,null)";
    $statement = $conn->prepare($query);
    $statement->execute($data);

    $id = $conn->lastInsertId();
    $path = 'images/';
    $file = $path . $id . ".png";
    $context = 'Audit Commission' . PHP_EOL . $id;
    QRcode::png($context, $file);

    $file_context = file_get_contents($file);
    $query = "UPDATE inventory set QR_Code = ? where Items_ID='" . $id . "'";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $file_context);
    $statement->execute();

    $output = array('message' => 'Insert completed');
    echo json_encode($output);
} else if ($received_data->action == 'update') {
    $path = 'images/';
    $file = $path . $received_data->Items_ID . ".png";
    $context = 'Audit Commission' . PHP_EOL . $received_data->Items_ID;
    QRcode::png($context, $file);
    $file_context = file_get_contents($file);

    $query = " UPDATE inventory SET owner_id = ?,QR_Code = ?, Type = ?, Model=? , Serial=? ,File_reference=?, Maintaince=? WHERE Items_ID = ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->owner_ID);
    $statement->bindParam(2, $file_context);
    $statement->bindParam(3, $received_data->Type);
    $statement->bindParam(4, $received_data->Model);
    $statement->bindParam(5, $received_data->Serial);
    $statement->bindParam(6, $received_data->FileRef);
    $statement->bindParam(7, $received_data->maintenance);
    $statement->bindParam(8, $received_data->Items_ID);
    $statement->execute();
    $output = array('message' => 'Data updated');

    echo json_encode($output);
} else if ($received_data->action == 'delete') {
    $query = " DELETE FROM inventory WHERE Items_ID = '" . $received_data->id . "'";
    $statement = $conn->prepare($query);

    $statement->execute();

    $output = array(
        'message' => 'Data Deleted'
    );

    echo json_encode($output);
} else if ($received_data->action == 'initial') {
    $query = "SELECT DISTINCT owner_division from owners";
    $statement = $conn->prepare($query);
    $statement->execute();
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        array_push($data, $row['owner_division']);
    }

    echo json_encode($data);
} else if ($received_data->action == 'initialPost') {
    $query = "SELECT DISTINCT owner_post from owners WHERE owner_division ='" . $received_data->division . "' order by owner_post ASC";
    $statement = $conn->prepare($query);
    $statement->execute();
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        array_push($data, $row['owner_post']);
    }

    echo json_encode($data);
} else if ($received_data->action == 'changeName') {
    $data = array(
        ':owner_division' => $received_data->division,
        ':owner_post' => $received_data->post
    );
    $query = "SELECT owner_name from owners where owner_division= :owner_division and owner_post=:owner_post";
    $statement = $conn->prepare($query);
    $statement->execute($data);
    $result = $statement->fetchAll();

    foreach ($result as $row) {
        $data['owner_name'] = $row['owner_name'];
    }

    echo json_encode($data);
} else if ($received_data->action == 'getEquipmentID') {

    $data = array(
        ':Type' => $received_data->Type,
        ':Model' => $received_data->Model,
        ':Serial' => $received_data->Serial
    );

    $query = "SELECT E_id from equipment where Type=:Type and Model=:Model and Serial=:Serial";
    $statement = $conn->prepare($query);
    $statement->execute($data);

    $result = $statement->fetchAll();

    $output = array();
    foreach ($result as $row) {
        array_push($output, $row['E_id']);
    }

    echo json_encode($output);
} else if ($received_data->action == 'getOwnerID') {
    $data = array(
        ':division' => $received_data->division,
        ':post' => $received_data->post,
        ':name' => $received_data->name
    );

    $query = "SELECT owner_id from owners where owner_division =:division and owner_post =:post and owner_name = :name";
    $statement = $conn->prepare($query);
    $statement->execute($data);

    $output = array();
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        array_push($output, $row['owner_id']);
    }

    echo json_encode($output);
} else if ($received_data->action == 'count') {
    $query = "SELECT count(*) from inventory";
    $statement = $conn->query($query);
    $count = $statement->fetchColumn();

    echo json_encode($count);
} else if ($received_data->action == 'SearchingCounter') {
    $query = " SELECT count(*) FROM inventory i , owners o 
    WHERE  o.owner_id = i.owner_id ";

    if ($received_data->Search_division != '') {
        $query = $query . " and o.owner_division like '%" . $received_data->Search_division . "%'";
    }

    if ($received_data->Search_post != '') {
        $query = $query . " and o.owner_post like '%" . $received_data->Search_post . "%'";
    }

    if ($received_data->Search_name != '') {
        $query = $query . " and o.owner_name like '%" . $received_data->Search_name . "%'";
    }

    if ($received_data->Search_Type != '') {
        $query = $query . " and i.Type like '%" . $received_data->Search_Type . "%'";
    }

    if ($received_data->Search_Model != '') {
        $query = $query . " and i.Model like '%" . $received_data->Search_Model . "%'";
    }

    if ($received_data->Search_Serial != '') {
        $query = $query . " and i.Serial like '%" . $received_data->Search_Serial . "%'";
    }

    $statement = $conn->query($query);
    $count = $statement->fetchColumn();

    echo json_encode($count);
} else if ($received_data->action == 'Login') {
    $query = "SELECT count(*) From administrator WHERE user_email ='" . $received_data->username . "' and user_password = '" . $received_data->password . "'";
    $statement = $conn->query($query);
    $count = $statement->fetchColumn();

    echo json_encode($count);
} else if ($received_data->action == 'initialEquipment') {
    $query = "SELECT DISTINCT Type from equipment order by Type ASC";

    $statement = $conn->prepare($query);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        array_push($data, $row['Type']);
    }

    echo json_encode($data);
} else if ($received_data->action == 'initialModel') {
    $query = "SELECT DISTINCT Model from equipment WHERE Type='" . $received_data->Type . "' order by Model ASC";
    $statement = $conn->prepare($query);
    $statement->execute();
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        array_push($data, $row['Model']);
    }

    echo json_encode($data);
} else if ($received_data->action == 'allId') {
    $query = "SELECT Items_ID from inventory";
    $statement = $conn->prepare($query);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        array_push($data, $row['Items_ID']);
    }

    echo json_encode($data);
} else if ($received_data->action == 'updateAll') {
    $path = 'images/';
    $file = $path . $received_data->id . ".png";
    $context = 'Audit Commission' . PHP_EOL . $received_data->id;
    QRcode::png($context, $file);
    $file_context = file_get_contents($file);

    $query = 'UPDATE inventory SET QR_Code = ? where Items_ID = ?';
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $file_context);
    $statement->bindParam(2, $received_data->id);
    $statement->execute();

    $data = array('message' => 'update all QRCode successful');

    echo json_encode($data);
} else if ($received_data->action == 'AllEquipment') {
    $start_of = $received_data->offset * ($received_data->currentPage - 1);
    $query = 'SELECT * from equipment where 1=1 ';

    if ($received_data->Type != '') {
        $query .= "and Type like '%" . $received_data->Type . "%' ";
    }

    if ($received_data->Model != '') {
        $query .= "and Model like '%" . $received_data->Model . "%' ";
    }

    $query .= ' order by Type ASC, Model ASC limit ?,? ';
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $start_of, PDO::PARAM_INT);
    $statement->bindParam(2, $received_data->offset, PDO::PARAM_INT);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }

    echo json_encode($data);
} else if ($received_data->action == 'addEquipment') {
    $query = 'INSERT INTO equipment values(null ,?, ?)';
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->Type);
    $statement->bindParam(2, $received_data->Model);
    $statement->execute();

    $data = array('message' => 'Equipment Inserted');

    echo json_encode($data);
} else if ($received_data->action == 'deleteEquipment') {
    $query = 'DELETE from equipment where E_id = ?';
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->id);
    $statement->execute();

    $data = array('message' => 'Deleted Equipment');

    echo json_encode($data);
} else if ($received_data->action == 'fetchEquipment') {
    $query = 'SELECT * from equipment where E_id=?';
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->id);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }

    echo json_encode($data);
} else if ($received_data->action == 'UpdateEquipment') {
    $query = 'UPDATE equipment set Type= ? , Model= ? where E_id= ?';
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->Type);
    $statement->bindParam(2, $received_data->Model);
    $statement->bindParam(3, $received_data->id);
    $statement->execute();

    $data = array('message' => 'Update Complete');
    echo json_encode($data);
} else if ($received_data->action == 'fetchEquipmentCount') {
    $query = 'SELECT count(*) as num from equipment where 1=1 ';

    if ($received_data->Type != '') {
        $query .= "and Type like '%" . $received_data->Type . "%' ";
    }

    if ($received_data->Model != '') {
        $query .= "and Model like '%" . $received_data->Model . "%' ";
    }
    $statement = $conn->prepare($query);
    $statement->execute();
    $num_rows = $statement->fetchColumn();

    echo json_encode($num_rows);
} else if ($received_data->action == 'FetchAllUser') {
    $start_of = $received_data->offset * ($received_data->currentPage - 1);
    $query = "SELECT * from owners where 1=1 ";

    if ($received_data->name != '') {
        $query .= " and owner_name like '%" . $received_data->name . "%'";
    }

    if ($received_data->post != '') {
        $query .= " and owner_post like '%" . $received_data->post . "%'";
    }

    if ($received_data->division != '') {
        $query .= " and owner_division like '%" . $received_data->division . "%'";
    }

    $query .= " order by owner_division ASC, owner_post ASC limit ?,?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $start_of, PDO::PARAM_INT);
    $statement->bindParam(2, $received_data->offset, PDO::PARAM_INT);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }

    echo json_encode($data);
} else if ($received_data->action == 'addUser') {
    $query = "INSERT INTO owners values(null, ?, ?, ?)";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->name);
    $statement->bindParam(2, $received_data->post);
    $statement->bindParam(3, $received_data->division);
    $statement->execute();

    $data = array('message' => 'User insert');
    echo json_encode($data);
} else if ($received_data->action == 'DeleteUser') {
    $query = "DELETE from owners where owner_id = ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->id);
    $statement->execute();

    $data = array('message' => 'Deleted User');
    echo json_encode($data);
} else if ($received_data->action == 'fetchUser') {
    $query = "SELECT * from owners where owner_id = ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->id);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $data[] = $row;
    }

    echo json_encode($data);
} else if ($received_data->action == 'updateUser') {
    $query = "UPDATE owners set owner_division = ?, owner_post = ?, owner_name=? where owner_id = ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->division);
    $statement->bindParam(2, $received_data->post);
    $statement->bindParam(3, $received_data->name);
    $statement->bindParam(4, $received_data->id);
    $statement->execute();

    $data = array('message' => 'Update user');
    echo json_encode($data);
} else if ($received_data->action == 'fetchAllUserCount') {
    $query = "SELECT count(*) as num from owners where 1=1 ";
    if ($received_data->name != '') {
        $query .= " and owner_name like '%" . $received_data->name . "%'";
    }

    if ($received_data->post != '') {
        $query .= " and owner_post like '%" . $received_data->post . "%'";
    }

    if ($received_data->division != '') {
        $query .= " and owner_division like '%" . $received_data->division . "%'";
    }
    $statement = $conn->prepare($query);
    $statement->execute();
    $num_rows = $statement->fetchColumn();

    echo json_encode($num_rows);
} else if ($received_data->action == 'UserExist') {
    $query = "SELECT count(*) from owners where user_name= ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->username);
    $statement->execute();

    $count = $statement->fetchColumn();

    echo json_encode($count);
} else if ($received_data->action == 'processUpdate') {
    $query = "UPDATE owners set user_password = ? where user_name = ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->newpassword);
    $statement->bindParam(2, $received_data->username);
    $statement->execute();

    $data = array('message' => 'Password updated');
    echo json_encode($data);
} else if ($received_data->action == 'sendemail') {
    $headers = 'From: webmaster@example.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

    $Content = "The request of Reset password has been received, Here's the confirmation code to finish the reset password process: " . "\r\n";
    $Content .= $received_data->code;
    mail($received_data->username, 'Reset Password', $Content, $headers);

    $data = array('message' => 'send email');
    echo json_encode($data);
} else if ($received_data->action == 'groupEditRef') {


    $query = "UPDATE inventory set File_reference = ?  where Type=? and Model=?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->FileRef);
    $statement->bindParam(2, $received_data->Type);
    $statement->bindParam(3, $received_data->Model);
    $statement->execute();



    $data = array('message' => 'Group File reference updated');

    echo json_encode($data);
} else if ($received_data->action == 'groupEditMaintenance') {
    $query = "UPDATE inventory set Maintaince = ?  where Type=? and Model=?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->maintenance);
    $statement->bindParam(2, $received_data->Type);
    $statement->bindParam(3, $received_data->Model);
    $statement->execute();

    $data = array('message' => 'Group Maintaince updated');

    echo json_encode($data);
} else if ($received_data->action == 'fetchByLoginSession') {
    $query = "SELECT i.Items_ID, o.owner_post, o.owner_name, o.owner_division, i.Type, i.Model, i.Serial, i.QR_Code, i.File_reference, i.Maintaince FROM inventory i , owners o
    WHERE i.owner_id = o.owner_id and o.owner_id = ?";

    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->owner_id);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        //echo json_encode($row);
        array_push($data, array(
            'Items_ID' => $row['Items_ID'],
            'owner_post' => $row['owner_post'],
            'owner_name' => $row['owner_name'],
            'owner_division' => $row['owner_division'],
            'Type' => $row['Type'],
            'Model' => $row['Model'],
            'Serial' => $row['Serial'],
            'QR_Code' => base64_encode($row['QR_Code']),
            'File_reference' => $row['File_reference'],
            'Maintaince' => $row['Maintaince']
        ));
    }

    echo json_encode($data);
} else if ($received_data->action == 'GroupUpdate') {
    $query = "UPDATE inventory set File_reference = ?, Maintaince=? where Items_ID = ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->File_reference);
    $statement->bindParam(2, $received_data->Maintaince);
    $statement->bindParam(3, $received_data->Items_ID);
    $statement->execute();

    $data = array('message' => 'Update success' . $received_data->Items_ID);
    echo json_encode($data);
} else if ($received_data->action == 'previewData') {
    $query =  " SELECT i.Items_ID, i.Type, i.Model, i.Serial, i.File_reference, i.Maintaince FROM inventory i , owners o
    WHERE i.owner_id = o.owner_id and i.Type=? and i.Model=?";

    $statement = $conn->prepare($query);
    $statement->bindParam(1, $received_data->Type);
    $statement->bindParam(2, $received_data->Model);
    $statement->execute();

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        array_push($data, array(
            'Items_ID' => $row['Items_ID'],
            'Type' => $row['Type'],
            'Model' => $row['Model'],
            'Serial' => $row['Serial'],
            'File_reference' => $row['File_reference'],
            'Maintaince' => $row['Maintaince']
        ));
    }

    echo json_encode($data);
}
