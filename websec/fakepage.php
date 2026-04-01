<div class="container">
 <form id='login' action="login.php" method='post' accept-charset='UTF-8'>
 <div class="form-group">
 <label for="username">Login User</label>
 <input type="text" name='userName' class="form-control" id="username" ariadescribedby="userNameHelp" placeholder="Enter username" maxlength="50" required />
 <small id="userNameHelp" class="form-text text-muted">Enter your username</small>
 </div>
 <div class="form-group">
    <label for="password">Password</label>
    <input type="password" name='password' class="form-control" id="password"
   placeholder="Password" maxlength="50" required />
    </div>
    <input type="submit" class="btn btn-primary" name='Submit' value='Login' />
    </form>
   </div>