<?php
require_once('sessions_handler.php');

class rankDB
{
    private $rankdb;

    public function __construct()
    {
        $this->rankdb = new mysqli("127.0.0.1", "testUser", "12345", "IT490");

        if ($this->rankdb->connect_errno != 0)
        {
            echo "Error connecting to the database: " . $this->db->connect_error . PHP_EOL;
            exit(1);
        }
        echo "Successfully connected to the database" . PHP_EOL;
    }

    public function getTop10Meals()
    {
        $statement = "SELECT * FROM meals ORDER BY rank_column DESC LIMIT 10";
        $response = $this->db->query($statement);

        $top10Rank = array();

        while ($row = $response->fetch_assoc())
        {
            $top10Rank[] = $row;
        }

        return $top10Rank;
    }
}

$top10DB = new Top10DB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) && $_POST['type'] === 'top10')
{
    $top10Rank = $top10DB->getTop10Meals();
    $responseArray = array('returnCode' => 1, 'top10' => $top10Rank);
    echo json_encode($responseArray);
}
else
{
    echo json_encode(array('returnCode' => 0, 'message' => 'Invalid request.'));
}
?>