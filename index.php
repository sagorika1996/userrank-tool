<!DOCTYPE html>
<html lang="en">
  <head>
    <title>User Rank</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://tools-static.wmflabs.org/cdnjs/ajax/libs/jquery/3.2.1/jquery.js"></script>
    <script src="https://tools-static.wmflabs.org/cdnjs/ajax/libs/twitter-bootstrap/4.0.0-beta/js/bootstrap.min.js"></script>
    <link rel = "stylesheet" type = "text/css" href = "index.css" />
  </head>

  <body>
    <div class="jumbotron text-center">
      <h1>Wikipedia Rank</h1> 
      <p>Enter a username and get the rank of user in bulgarian wikipedia</p> 
      <form class="form-inline" id="myForm" action="" method="post">
        <div class="input-group">
          <input name="searchTerm" type="text" class="form-control" size="50" placeholder="Username" required>
          <div class="input-group-btn">
            <button class="btn btn-default" type="submit"> 
              <i class="glyphicon glyphicon-search"></i>
            </button>
          </div>
        </div>
      </form>
    </div>

    <?php
      if(isset($_POST['searchTerm']))  { 
        $ts_pw = posix_getpwuid(posix_getuid());
        $ts_mycnf = parse_ini_file($ts_pw['dir'] . "/replica.my.cnf");

        $mysqli = new mysqli('bgwiki.labsdb', $ts_mycnf['user'], $ts_mycnf['password'], 'bgwiki_p');

        /* check connection */
        if ($mysqli->connect_error) {
          die("Connection failed: " . $mysqli->connect_error);
        }

        $username = $_POST["searchTerm"];

        $sql1 = "SELECT count(*) as count from user where user_name = '$username'";
        $res1 = $mysqli->query($sql1);

        if ($res1 == false) {
          echo 'The query failed.';
          exit();
        }

        $user_exists = 0;

        if ($res1->num_rows > 0) {
          while($row = $res1->fetch_assoc()) {
            $user_exists = $row["count"];
          }
        }

        if ($user_exists == 0) {
          echo "<div class='panel panel-default col-xs-4' > <div class='panel-body'> Username invalid </div></div>";
        }
        else {
          $sql2 = "SELECT count(*) as rank FROM user WHERE user_editcount > (SELECT user_editcount FROM user WHERE user_name = '$username')";
          $res2 = $mysqli->query($sq2);

          if ($res2 == false) {
            echo 'The query failed.';
            exit();
          }

          $sql3 = "SELECT count(*) as count from user";
          $res3 = $mysqli->query($sql3);

          if ($res3 == false) {
            echo 'The query failed.';
            exit();
          }

          $total_users = 0;

          if ($res3->num_rows > 0) {
            while($row = $res3->fetch_assoc()) {
              $total_users = $row["count"];
            }
          }

          if ($res2->num_rows > 0) {
            while($row = $res2->fetch_assoc()) {
              echo "<div class='panel panel-default col-xs-4' > <div class='panel-body'> Username: ".$username."<br> Rank: ".$row["rank"]."<br>Total number of users: ".$total_users."</div></div>";
            }
          }
        }
        $mysqli->close();
      }
    ?>

  </body>
</html>
