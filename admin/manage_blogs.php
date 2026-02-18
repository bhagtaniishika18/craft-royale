<?php
session_start();
include 'cache-headers.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('../includes/db.php');
$result = mysqli_query($conn, "SELECT * FROM blog_posts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Manage Blogs - Craft Royale</title>
    <?php include 'favicon.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(47, 199, 180, 0.3);
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(47, 199, 180, 0.4);
        }

        .blogs-table-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid rgba(47, 199, 180, 0.1);
        }

        .blogs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .blogs-table thead {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
        }

        .blogs-table th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .blogs-table td {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .blogs-table tbody tr {
            transition: all 0.3s ease;
        }

        .blogs-table tbody tr:hover {
            background: rgba(47, 199, 180, 0.05);
        }

        .blogs-table tbody tr:last-child td {
            border-bottom: none;
        }

        .blog-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid rgba(47, 199, 180, 0.2);
        }

        .blog-title {
            font-weight: 600;
            color: #2b2b2b;
            font-size: 16px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-edit, .btn-delete {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-edit {
            background: rgba(47, 199, 180, 0.1);
            color: #2fc7b4;
            border: 1px solid rgba(47, 199, 180, 0.3);
        }

        .btn-edit:hover {
            background: #2fc7b4;
            color: #fff;
        }

        .btn-delete {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .btn-delete:hover {
            background: #dc3545;
            color: #fff;
        }
        
        .btn-view:hover {
            background: #2fc7b4;
            color: #fff;
        }
        
        /* Blog View Modal */
        .blog-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            overflow-y: auto;
            padding: 20px;
        }
        
        .blog-modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .blog-modal {
            background: #fff;
            border-radius: 16px;
            max-width: 900px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            position: relative;
            margin: auto;
        }
        
        .blog-modal-header {
            background: linear-gradient(135deg, #2fc7b4, #2fa76b);
            color: #fff;
            padding: 20px 30px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .blog-modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        
        .blog-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            font-size: 24px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .blog-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }
        
        .blog-modal-body {
            padding: 30px;
        }
        
        .blog-view-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .blog-view-title {
            font-size: 28px;
            font-weight: 700;
            color: #2b2b2b;
            margin-bottom: 15px;
        }
        
        .blog-view-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .blog-view-content {
            font-size: 16px;
            line-height: 1.8;
            color: #2b2b2b;
            white-space: pre-wrap;
        }
        
        .blog-modal-loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .blog-modal-loading i {
            font-size: 48px;
            color: #2fc7b4;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ddd;
        }

        .success-message {
            background: #efe;
            color: #3c3;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Manage Blogs</h1>
                    <p class="welcome-text">View and manage all your blog posts</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo $_SESSION['admin']; ?></span>
                    </div>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> 
                        Blog post <?= $_GET['success'] == 'added' ? 'added' : 'updated' ?> successfully!
                    </div>
                <?php endif; ?>

                <div class="page-header">
                    <h2 class="page-title">Blogs List</h2>
                    <a href="add_blog.php" class="add-btn">
                        <i class="fas fa-plus"></i>
                        Add New Blog Post
                    </a>
                </div>

                <div class="blogs-table-container">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <table class="blogs-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Blog Title</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($row['image'])): ?>
                                                <img src="../uploads/blogs/<?php echo htmlspecialchars($row['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                                     class="blog-image" 
                                                     onerror="this.src='../assets/images/beads.jpg'">
                                            <?php else: ?>
                                                <div style="width: 80px; height: 80px; background: #f0f0f0; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #999;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="blog-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" class="btn-view view-blog-btn" data-blog-id="<?php echo $row['id']; ?>" style="padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 6px; background: rgba(47, 199, 180, 0.1); color: #2fc7b4; border: 1px solid rgba(47, 199, 180, 0.3); cursor: pointer;">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <a href="add_blog.php?id=<?php echo $row['id']; ?>" class="btn-edit">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="delete_blog.php?id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this blog post?');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-blog"></i>
                            <h3>No blog posts found</h3>
                            <p>Start by adding your first blog post!</p>
                            <a href="add_blog.php" class="add-btn" style="margin-top: 20px; display: inline-flex;">
                                <i class="fas fa-plus"></i>
                                Add Blog Post
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Blog View Modal -->
    <div class="blog-modal-overlay" id="blogModal">
        <div class="blog-modal">
            <div class="blog-modal-header">
                <h2>Blog Details</h2>
                <button class="blog-modal-close" onclick="closeBlogModal()">&times;</button>
            </div>
            <div class="blog-modal-body" id="blogModalBody">
                <div class="blog-modal-loading">
                    <i class="fas fa-spinner"></i>
                    <p>Loading blog details...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function openBlogModal(blogId) {
        const modal = document.getElementById('blogModal');
        const modalBody = document.getElementById('blogModalBody');
        
        modal.classList.add('active');
        modalBody.innerHTML = '<div class="blog-modal-loading"><i class="fas fa-spinner"></i><p>Loading blog details...</p></div>';
        
        fetch(`get_blog_details.php?id=${blogId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const blog = data.blog;
                    
                    let html = '';
                    if (blog.image_path) {
                        html += `<img src="${blog.image_path}" alt="${blog.title}" class="blog-view-image" onerror="this.src='../assets/images/beads.jpg'">`;
                    }
                    html += `
                        <h1 class="blog-view-title">${blog.title}</h1>
                        <div class="blog-view-meta">
                            <span><i class="fas fa-calendar"></i> ${blog.formatted_date}</span>
                            <span><i class="fas fa-tag"></i> ${blog.status ? blog.status.charAt(0).toUpperCase() + blog.status.slice(1) : 'N/A'}</span>
                        </div>
                        <div class="blog-view-content">${blog.content || 'No content available.'}</div>
                    `;
                    
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = `<div class="blog-modal-loading"><p style="color: #e74c3c;">${data.message || 'Failed to load blog details'}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div class="blog-modal-loading"><p style="color: #e74c3c;">An error occurred while loading blog details.</p></div>';
            });
    }
    
    function closeBlogModal() {
        document.getElementById('blogModal').classList.remove('active');
    }
    
    // Close modal on overlay click
    document.getElementById('blogModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeBlogModal();
        }
    });
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBlogModal();
        }
    });
    
    // Add click handlers to view buttons
    document.querySelectorAll('.view-blog-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const blogId = this.getAttribute('data-blog-id');
            openBlogModal(blogId);
        });
    });
    </script>
</body>
</html>







