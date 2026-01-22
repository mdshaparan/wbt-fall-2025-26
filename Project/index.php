<?php
session_start();

include 'db.php';

$internships_query = "SELECT * FROM internships WHERE status = 'open' ORDER BY created_at DESC LIMIT 10";
$internships_result = mysqli_query($conn, $internships_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InternConnectBD</title>
    <style>
        * {
            margin: 0px;
            padding: 0px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background: white;
            color: #333333;
            line-height: 1.5;
        }
        
     
        .header {
            background: white;
            padding: 15px 20px;
            border-bottom: 1px solid #eeeeee;
        }
        
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            color: blue;
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo span {
            color: green;
        }
        
        .links {
            display: flex;
            gap: 20px;
        }
        
        .links a {
            color: #666666;
            text-decoration: none;
            font-size: 16px;
        }
        
        .links a:hover {
            color: blue;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
        }
        
        .signin {
            background: white;
            color: blue;
            border: 1px solid blue;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .signin:hover {
            background: blue;
            color: white;
        }
        
        .create {
            background: blue;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .create:hover {
            background: darkblue;
        }
        
  
        .search-section {
            padding: 30px 20px;
            background: #f5f5f5;
            text-align: center;
        }
        
        .search-title {
            color: darkblue;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .search-form {
            max-width: 800px;
            margin: 0px auto;
        }
        
        .search-input {
            width: 400px;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .search-input:hover {
            border-color: blue;
        }
        
        .search-btn {
            background: green;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .search-btn:hover {
            background: darkgreen;
        }
        
       
        .hero {
            padding: 40px 20px;
            text-align: center;
            background: white;
        }
        
        .hero h2 {
            color: darkblue;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .hero p {
            color: #666666;
            width: 700px;
            margin: 0px auto 20px auto;
            text-align: center;
        }
        
        .hero-btn {
            background: blue;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .hero-btn:hover {
            background: darkblue;
        }
        
      
        .promo {
            padding: 30px 20px;
            background: #e8f4ff;
            text-align: center;
            margin: 20px 0px;
        }
        
        .promo-title {
            color: darkblue;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .promo-subtitle {
            color: green;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
      
        .hot-jobs-section {
            padding: 40px 20px;
            background: #f9f9f9;
        }
        
        .jobs-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .job-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .job-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .job-card:hover {
            border-color: blue;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .job-title {
            color: darkblue;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .job-company {
            color: #666;
            margin-bottom: 10px;
        }
        
        .job-skills {
            color: #888;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .job-duration {
            color: green;
            font-weight: bold;
        }
        
       
        .categories-section {
            padding: 40px 20px;
            background: white;
        }
        
        .section-title {
            text-align: center;
            color: darkblue;
            margin-bottom: 30px;
            font-size: 24px;
        }
        
        .categories {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            max-width: 1000px;
            margin: 0px auto;
        }
        
        .category {
            width: 220px;
            text-align: center;
            padding: 20px 10px;
            border: 1px solid #eeeeee;
            border-radius: 6px;
            background: white;
        }
        
        .category:hover {
            border-color: blue;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .category h3 {
            color: #333333;
            margin-bottom: 10px;
        }
        
        .category h3:hover {
            color: blue;
        }
        
        .category p {
            color: #666666;
            font-size: 14px;
        }
        
      
        .footer {
            background: rgba(77, 106, 119, 1);
            padding: 20px;
            text-align: center;
            margin-top: 30px;
            border-top: 1px solid #eeeeee;
        }
        
        .copyright {
            color: #ffffffff;
            font-size: 14px;
        }
    </style>
</head>
<body>
    
    <div class="header">
        <div class="nav">
            <div class="logo">InternConnect<span>BD</span></div>
            
            <div class="links">
                <a href="#hot-jobs">Hot Jobs</a>
                <a href="#categories">Categories</a>
                
            </div>
            
            <div class="buttons">
                <button class="signin" onclick="window.location.href='login.php'">SIGN IN</button>
                <button class="create" onclick="window.location.href='register.php'">CREATE ACCOUNT</button>
            </div>
        </div>
    </div>
        
    <div class="promo">
        <h3 class="promo-title">UNLIMITED INTERNSHIP OPPORTUNITIES</h3>
        <p class="promo-subtitle">FOR STUDENTS AND FRESH GRADUATES</p>
    </div>
    
    <div class="search-section">
        <h2 class="search-title">Search for internships...</h2>
        <div class="search-form">
            <input type="text" class="search-input" placeholder="Job title, skills, keywords">
            <button class="search-btn">Search</button>
        </div>
    </div>

    <div class="hero">
        <h2>Find Your Perfect Internship</h2>
        <p>Connect with top companies in Bangladesh. Find internships that match your skills and location. Start your career journey today with verified opportunities.</p>
        <button class="hero-btn" onclick="window.location.href='#hot-jobs'">Find Internships Now</button>
    </div>

    <!-- Hot Jobs Section -->
    <div class="hot-jobs-section" id="hot-jobs">
        <div class="jobs-container">
            <h2 class="section-title">Latest Internship Opportunities</h2>
            <div class="job-list">
                <?php if(mysqli_num_rows($internships_result) > 0): ?>
                    <?php while($internship = mysqli_fetch_assoc($internships_result)): ?>
                        <div class="job-card">
                            <h3 class="job-title"><?php echo htmlspecialchars($internship['title']); ?></h3>
                            <p class="job-company">Company ID: <?php echo $internship['company_id']; ?></p>
                            <p class="job-skills">Skills: <?php echo htmlspecialchars($internship['required_skills']); ?></p>
                            <p class="job-duration">Duration: <?php echo $internship['duration_months']; ?> months</p>
                            <p>Deadline: <?php echo date('Y-m-d', strtotime($internship['application_deadline'])); ?></p>
                            <button style="margin-top: 10px; background: green; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;" 
                                    onclick="window.location.href='apply.php?internship_id=<?php echo $internship['id']; ?>'">
                                Apply Now
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%;">No internships available at the moment. Check back later!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    <div class="categories-section" id="categories">
        <h2 class="section-title">Browse By Popular Categories</h2>
        <div class="categories">
            <div class="category">
                <h3>Engineering</h3>
                <p>Civil, Electrical, Mechanical</p>
            </div>
            
            <div class="category">
                <h3>IT & Software</h3>
                <p>Development, Testing, Design</p>
            </div>
            
            <div class="category">
                <h3>Business</h3>
                <p>Marketing, Finance, HR</p>
            </div>
            
            <div class="category">
                <h3>Healthcare</h3>
                <p>Medical, Pharmacy, Nursing</p>
            </div>
            
            <div class="category">
                <h3>Design</h3>
                <p>UI/UX, Graphic, Creative</p>
            </div>
            
            <div class="category">
                <h3>Content</h3>
                <p>Writing, Editing, Media</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p class="copyright">© 2025 InternConnectBD. All rights reserved.</p>
    </div>

</body>
</html>
<?php mysqli_close($conn); ?>