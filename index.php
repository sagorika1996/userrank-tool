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
      function getRank() {
        if(isset($_POST['searchTerm']))  { 
          $ts_pw = posix_getpwuid(posix_getuid());
          $ts_mycnf = parse_ini_file($ts_pw['dir'] . "/replica.my.cnf");

          $mysqli = new mysqli('bgwiki.labsdb', $ts_mycnf['user'], $ts_mycnf['password'], 'bgwiki_p');

          /* check connection */
          if ($mysqli->connect_error) {
            echo "Connection failed: " . $mysqli->connect_error;
            return;
          }

          $username = $_POST["searchTerm"];

          $sql1 = $mysqli->prepare("SELECT count(*) as count from user where user_name = ?");
          $sql1->bind_param('s', $username);
          $sql1->execute();
          $res1 = $sql1->get_result();

          if ($res1 == false) {
            echo 'The query failed.';
            return;
          }

          $user_exists = $res1->fetch_assoc()["count"];

          if ($user_exists == 0) {
            echo "<div class='panel panel-default col-xs-4' > <div class='panel-body'> No such user </div></div>";
          }
          else {
            $sql2 = $mysqli->prepare("SELECT count(*) as rank FROM user WHERE user_editcount > (SELECT user_editcount FROM user WHERE user_name = ?)");
            $sql2->bind_param('s', $username);
            $sql2->execute();
            $res2 = $sql2->get_result();

            if ($res2 == false) {
              echo 'The query failed.';
              return;
            }

            $sql3 = "SELECT count(*) as count from user";
            $res3 = $mysqli->query($sql3);

            if ($res3 == false) {
              echo 'The query failed.';
              return;
            }

            $total_users = $res3->fetch_assoc()["count"];

            echo "<div class='panel panel-default col-xs-4' > <div class='panel-body'> Username: ".$username."<br> Rank: ".($res2->fetch_assoc()["rank"]+1)."<br>Total number of users: ".$total_users."</div></div>";
          }
          $mysqli->close();
        }
        return;
      }

      getRank();
    ?>

  </body>
</html>
