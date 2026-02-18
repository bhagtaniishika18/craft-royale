<?php
session_start();

// Strong cache prevention headers
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../includes/db.php';

// Handle success/error messages
$success_message = $_GET['success'] ?? '';
$error_message = $_GET['error'] ?? '';

// Get all blogs
$result = mysqli_query($conn, "SELECT * FROM blog_posts ORDER BY created_at DESC");

// Check if query was successful
$has_blogs = false;
$blogs_count = 0;
if ($result) {
    $blogs_count = mysqli_num_rows($result);
    $has_blogs = $blogs_count > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>All Blogs - Craft Royale</title>
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
            color: #1e293b;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-left: 5px solid #8B5CF6;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .add-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.25);
            border: none;
        }

        .add-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 30px rgba(139, 92, 246, 0.4);
            filter: brightness(1.1);
        }

        .blogs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-top: 10px;
        }

        .blog-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #f1f5f9;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            position: relative;
        }

        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.08);
            border-color: #e2e8f0;
        }

        .blog-image-container {
            width: 100%;
            height: 220px;
            overflow: hidden;
            background: #f1f5f9;
            position: relative;
        }

        .blog-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .blog-card:hover .blog-image-container img {
            transform: scale(1.1);
        }

        .blog-content {
            padding: 25px;
        }

        .blog-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 15px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 52px;
        }

        .blog-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            font-size: 13px;
            color: #64748b;
            font-weight: 600;
        }

        .blog-status {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .blog-status.active {
            background: #ecfdf5;
            color: #10b981;
            border: 1px solid #d1fae5;
        }

        .blog-status.inactive {
            background: #fff1f2;
            color: #f43f5e;
            border: 1px solid #ffe4e6;
        }

        .blog-actions {
            display: flex;
            gap: 12px;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }

        .btn-action {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-view {
            background: #f1f5f9;
            color: #64748b;
        }

        .btn-view:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: scale(1.05);
        }

        .btn-edit {
            background: #eff6ff;
            color: #3b82f6;
        }

        .btn-edit:hover {
            background: #3b82f6;
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .btn-delete {
            background: #fff1f2;
            color: #f43f5e;
        }

        .btn-delete:hover {
            background: #f43f5e;
            color: #fff;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.2);
        }

        /* Modern Deletion Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .custom-active-modal {
            background: #fff;
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .modal-overlay.active .custom-active-modal {
            transform: scale(1);
        }

        .modal-icon {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 25px;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .modal-title {
            font-size: 24px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .modal-desc {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .modal-btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 15px;
        }

        .btn-confirm {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .btn-confirm:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateY(-2px);
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .alert-error {
            background: #fff1f2;
            color: #e11d48;
            border: 1px solid #fecaca;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Blog Manager</h1>
                    <p class="welcome-text">Curate and publish premium articles</p>
                </div>
                <div class="header-right">
                    <a href="add_blog.php" class="add-btn">
                        <i class="fas fa-plus"></i>
                        New Blog Post
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($success_message): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($success_message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error_message) ?></span>
                    </div>
                <?php endif; ?>

                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Articles</h3>
                        <p class="stat-number"><?= $blogs_count ?></p>
                    </div>
                    <div class="stat-card" style="border-left-color: #10b981;">
                        <h3>Active Posts</h3>
                        <p class="stat-number"><?= mysqli_num_rows(mysqli_query($conn, "SELECT id FROM blog_posts WHERE status='active'")) ?></p>
                    </div>
                    <div class="stat-card" style="border-left-color: #f59e0b;">
                        <h3>Latest Update</h3>
                        <p class="stat-number" style="font-size: 16px; margin-top: 15px;"><?= $has_blogs ? date('j M Y') : 'N/A' ?></p>
                    </div>
                </div>

                <?php if (!$result): ?>
                    <div class="alert-error" style="background: #fff1f2; color: #e11d48; border: 1px solid #fecaca;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div>
                            <strong>Database Error</strong>
                            <p style="margin: 5px 0 0 0; font-size: 14px;">Unable to fetch blogs. Please check your database connection.</p>
                            <p style="margin: 5px 0 0 0; color: #666; font-size: 12px;"><?= mysqli_error($conn) ?></p>
                        </div>
                    </div>
                <?php elseif ($has_blogs): ?>
                    <div class="blogs-grid">
                        <?php while($row = mysqli_fetch_assoc($result)): 
                            $blog_title = $row['title'] ?? 'Untitled Blog';
                            $blog_image = $row['image'] ?? '';
                            $blog_id = $row['id'];
                            $blog_status = $row['status'] ?? 'inactive';
                            $blog_date = date('M d, Y', strtotime($row['created_at']));
                            
                            // Handle image path
                            $image_path = '../uploads/blogs/' . $blog_image;
                            $display_image = (!empty($blog_image) && file_exists($image_path)) ? $image_path : '../assets/images/beads.jpg';
                        ?>
                            <div class="blog-card">
                                <div class="blog-image-container">
                                    <img src="<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($blog_title) ?>" onerror="this.src='../assets/images/beads.jpg'">
                                </div>
                                <div class="blog-content">
                                    <h3 class="blog-title"><?= htmlspecialchars($blog_title) ?></h3>
                                    <div class="blog-meta">
                                        <span><i class="fas fa-calendar" style="color: #8B5CF6; margin-right: 8px;"></i> <?= $blog_date ?></span>
                                        <span class="blog-status <?= $blog_status ?>"><?= ucfirst($blog_status) ?></span>
                                    </div>
                                    <div class="blog-actions">
                                        <button type="button" class="btn-action btn-view view-blog-btn" data-blog-id="<?= $blog_id ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <a href="add_blog.php?id=<?= $blog_id ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button onclick="confirmDeleteBlog(<?= $blog_id ?>)" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="text-align: center; padding: 60px 20px; background: white; border-radius: 24px; border: 1px solid #f1f5f9;">
                        <i class="fas fa-blog" style="font-size: 64px; color: #e2e8f0; margin-bottom: 20px;"></i>
                        <h3 style="color: #64748b;">No blog posts found</h3>
                        <p style="color: #94a3b8; margin-bottom: 25px;">Start by adding your first premium blog post!</p>
                        <a href="add_blog.php" class="add-btn">
                            <i class="fas fa-plus"></i>
                            Add Blog Post
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Blog View Modal -->
    <div class="blog-modal-overlay" id="blogModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px); z-index: 10000; overflow-y: auto; padding: 20px;">
        <div class="blog-modal" style="background: #fff; border-radius: 24px; max-width: 900px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2); position: relative; margin: auto;">
            <div class="blog-modal-header" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED); color: #fff; padding: 25px 35px; border-radius: 24px 24px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 24px; font-weight: 800;">Article Preview</h2>
                <button class="blog-modal-close" onclick="closeBlogModal()" style="background: rgba(255, 255, 255, 0.2); border: none; color: #fff; font-size: 24px; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; transition: all 0.3s ease;">&times;</button>
            </div>
            <div class="blog-modal-body" id="blogModalBody" style="padding: 40px;">
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-spinner" style="font-size: 48px; color: #8B5CF6; animation: spin 1s linear infinite;"></i>
                    <p>Fetching article content...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modern Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="custom-active-modal">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="modal-title">Confirm Deletion</h2>
            <p class="modal-desc">Are you sure you want to delete this blog post? This action is permanent and cannot be undone.</p>
            <div class="modal-actions">
                <button class="modal-btn btn-cancel" onclick="closeDeleteModal()">No, Keep it</button>
                <button onclick="submitDelete()" class="modal-btn btn-confirm" style="display: flex; align-items: center;">Yes, Delete it</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for secure deletion -->
    <form id="deleteForm" method="POST" action="delete_blog.php" style="display: none;">
        <input type="hidden" name="id" id="deleteId">
        <input type="hidden" name="confirm_delete" value="1">
    </form>

    <script>
    function confirmDeleteBlog(blogId) {
        const modal = document.getElementById('deleteModal');
        document.getElementById('deleteId').value = blogId;
        modal.classList.add('active');
    }

    function submitDelete() {
        document.getElementById('deleteForm').submit();
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('active');
    }

    function openBlogModal(blogId) {
        const modal = document.getElementById('blogModal');
        const modalBody = document.getElementById('blogModalBody');
        
        modal.style.display = 'flex';
        modalBody.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;"><i class="fas fa-spinner" style="font-size: 48px; color: #8B5CF6; animation: spin 1s linear infinite;"></i><p>Loading blog details...</p></div>';
        
        fetch(`get_blog_details.php?id=${blogId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const blog = data.blog;
                    
                    let html = '';
                    if (blog.image_path) {
                        html += `<img src="${blog.image_path}" alt="${blog.title}" style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);" onerror="this.src='../assets/images/beads.jpg'">`;
                    }
                    html += `
                        <h1 style="font-size: 28px; font-weight: 800; color: #1e293b; margin-bottom: 15px;">${blog.title}</h1>
                        <div style="display: flex; gap: 20px; margin-bottom: 25px; color: #64748b; font-size: 14px; font-weight: 600;">
                            <span><i class="fas fa-calendar" style="color: #8B5CF6; margin-right: 8px;"></i> ${blog.formatted_date}</span>
                            <span class="blog-status ${blog.status}" style="font-size: 11px;">${blog.status ? blog.status.charAt(0).toUpperCase() + blog.status.slice(1) : 'N/A'}</span>
                        </div>
                        <div style="font-size: 16px; line-height: 1.8; color: #475569; white-space: pre-wrap; background: #f8fafc; padding: 25px; border-radius: 16px; border: 1px solid #f1f5f9;">${blog.content || 'No content available.'}</div>
                    `;
                    
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = `<div style="text-align: center; padding: 40px;"><p style="color: #ef4444;">${data.message || 'Failed to load blog details'}</p></div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><p style="color: #ef4444;">An error occurred while loading blog details.</p></div>';
            });
    }
    
    function closeBlogModal() {
        document.getElementById('blogModal').style.display = 'none';
    }

    // Close modals if clicking outside
    window.onclick = function(event) {
        const delModal = document.getElementById('deleteModal');
        const blogModal = document.getElementById('blogModal');
        if (event.target == delModal) {
            closeDeleteModal();
        }
        if (event.target == blogModal) {
            closeBlogModal();
        }
    }
    
    document.querySelectorAll('.view-blog-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const blogId = this.getAttribute('data-blog-id');
            openBlogModal(blogId);
        });
    });
    </script>
</body>
</html>
