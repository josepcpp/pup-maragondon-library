<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_auth();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Manual — PUP Maragondon Library CMS</title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <style>
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Source Sans 3',ui-sans-serif,system-ui,sans-serif;font-size:15px;color:#241f1c;background:#f7f5f1;line-height:1.7;}
    a{color:#800000;text-decoration:none;}
    a:hover{text-decoration:underline;}

    /* ── Layout ── */
    .manual-wrap{display:grid;grid-template-columns:260px 1fr;min-height:100vh;}
    .sidebar{background:#241f1c;color:#d4d4e8;position:sticky;top:0;height:100vh;overflow-y:auto;padding:0 0 40px;}
    .main{padding:48px 56px;max-width:900px;}

    /* ── Sidebar ── */
    .sb-logo{padding:24px 20px 16px;border-bottom:1px solid rgba(255,255,255,0.08);margin-bottom:8px;}
    .sb-logo h1{font-size:0.9rem;color:#fff;font-weight:700;line-height:1.4;}
    .sb-logo p{font-size:0.72rem;color:#888;margin-top:2px;}
    .sb-section{padding:8px 20px 4px;font-size:0.65rem;letter-spacing:2px;text-transform:uppercase;color:#666;font-weight:700;margin-top:8px;}
    .sb-link{display:flex;align-items:center;gap:10px;padding:8px 20px;font-size:0.8rem;color:#b0b0c8;cursor:pointer;transition:background 0.15s;}
    .sb-link:hover{background:rgba(255,255,255,0.06);color:#fff;text-decoration:none;}
    .sb-link i{width:16px;text-align:center;font-size:0.75rem;opacity:0.7;}
    .sb-print{margin:20px;padding:10px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:8px;font-size:0.78rem;color:#aaa;text-align:center;cursor:pointer;}
    .sb-print:hover{background:rgba(255,255,255,0.12);color:#fff;}

    /* ── Typography ── */
    h1.page-title{font-size:2rem;font-weight:700;color:#241f1c;margin-bottom:6px;}
    .page-subtitle{color:#666;font-size:0.95rem;margin-bottom:40px;padding-bottom:24px;border-bottom:2px solid #e8e0d0;}
    h2.sec-title{font-size:1.3rem;font-weight:700;color:#800000;margin:48px 0 6px;padding-bottom:10px;border-bottom:2px solid #e4ded5;display:flex;align-items:center;gap:10px;}
    h2.sec-title i{font-size:1rem;opacity:0.8;}
    h3.sub-title{font-size:1rem;font-weight:600;color:#241f1c;margin:24px 0 8px;}
    p{margin-bottom:12px;color:#333;}
    .lead{font-size:1rem;color:#444;line-height:1.8;background:#fafaf7;border-left:4px solid #c9a84c;padding:14px 18px;border-radius:0 8px 8px 0;margin-bottom:20px;}

    /* ── Steps ── */
    .steps{counter-reset:step;display:flex;flex-direction:column;gap:10px;margin:16px 0 24px;}
    .step{display:flex;gap:14px;align-items:flex-start;}
    .step-num{counter-increment:step;flex-shrink:0;width:28px;height:28px;background:#800000;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;margin-top:2px;}
    .step-body{flex:1;}
    .step-body strong{display:block;font-size:0.9rem;color:#241f1c;margin-bottom:2px;}
    .step-body span{font-size:0.85rem;color:#555;}

    /* ── Fields table ── */
    .fields-table{width:100%;border-collapse:collapse;margin:16px 0 24px;font-size:0.85rem;}
    .fields-table th{background:#241f1c;color:#fff;padding:10px 14px;text-align:left;font-weight:600;font-size:0.78rem;}
    .fields-table td{padding:10px 14px;border-bottom:1px solid #e8e0d0;vertical-align:top;}
    .fields-table tr:nth-child(even) td{background:#fafaf7;}
    .fields-table .req{color:#c0392b;font-weight:700;}
    .fields-table .opt{color:#666;}

    /* ── Note / Warning / Tip boxes ── */
    .note,.warn,.tip{padding:14px 18px;border-radius:8px;margin:16px 0 24px;font-size:0.85rem;display:flex;gap:12px;align-items:flex-start;}
    .note{background:#e8f4fd;border:1px solid #bbd6ef;}
    .warn{background:#fdf3e8;border:1px solid #f0c080;}
    .tip{background:#eaf7ee;border:1px solid #90d4a0;}
    .note i{color:#2980b9;margin-top:2px;}
    .warn i{color:#e67e22;margin-top:2px;}
    .tip i{color:#27ae60;margin-top:2px;}
    .note p,.warn p,.tip p{margin:0;line-height:1.6;}

    /* ── Badges ── */
    .badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:0.72rem;font-weight:600;}
    .badge-req{background:#fde;color:#c0392b;}
    .badge-opt{background:#eee;color:#666;}
    .badge-nav{background:#241f1c;color:#fff;font-family:monospace;font-size:0.78rem;padding:3px 8px;border-radius:4px;}

    /* ── Menu path ── */
    .menu-path{display:inline-flex;align-items:center;gap:6px;background:#f0ece4;padding:6px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;color:#241f1c;margin-bottom:16px;}
    .menu-path i{color:#800000;font-size:0.7rem;}

    /* ── Divider ── */
    .divider{border:none;border-top:1px solid #e8e0d0;margin:40px 0;}

    /* ── TOC ── */
    .toc{background:#fff;border:1px solid #e8e0d0;border-radius:12px;padding:24px 28px;margin-bottom:40px;}
    .toc h3{font-size:0.85rem;text-transform:uppercase;letter-spacing:2px;color:#888;font-weight:700;margin-bottom:14px;}
    .toc ol{padding-left:20px;display:grid;grid-template-columns:1fr 1fr;gap:4px 24px;}
    .toc li{font-size:0.85rem;padding:3px 0;}
    .toc a{color:#800000;}

    /* ── Cover page ── */
    .cover{background:#5c0000;color:#fff;padding:60px 56px;margin:-48px -56px 48px;border-radius:0;}
    .cover h1{font-size:2.4rem;font-weight:700;margin-bottom:8px;}
    .cover p{opacity:0.8;font-size:1rem;}
    .cover .cover-meta{display:flex;gap:24px;margin-top:28px;padding-top:24px;border-top:1px solid rgba(255,255,255,0.15);}
    .cover .cover-meta-item{font-size:0.8rem;opacity:0.7;}
    .cover .cover-meta-item strong{display:block;font-size:1rem;opacity:1;font-weight:600;}

    /* ── Print ── */
    @media print{
      .sidebar,.sb-print{display:none!important;}
      .manual-wrap{display:block;}
      .main{padding:20px;max-width:100%;}
      .cover{margin:0 0 32px;padding:40px;border-radius:8px;print-color-adjust:exact;-webkit-print-color-adjust:exact;}
      h2.sec-title{break-before:avoid;}
      .steps,.fields-table,.note,.warn,.tip{break-inside:avoid;}
    }
    /* ── PDF generation state ── */
    .pdf-generating .sidebar{display:none!important;}
    .pdf-generating .manual-wrap{display:block!important;}
    .pdf-generating .main{padding:20px!important;max-width:100%!important;}
    /* Loading overlay */
    #pdf-overlay{display:none;position:fixed;inset:0;background:rgba(26,26,46,0.85);z-index:9999;align-items:center;justify-content:center;flex-direction:column;gap:16px;color:#fff;}
    #pdf-overlay.show{display:flex;}
    #pdf-overlay p{font-size:0.95rem;opacity:0.85;}
    .pdf-spinner{width:44px;height:44px;border:4px solid rgba(255,255,255,0.2);border-top-color:#c9a84c;border-radius:50%;animation:spin 0.8s linear infinite;}
    @keyframes spin{to{transform:rotate(360deg);}}
    @media(max-width:780px){
      .manual-wrap{grid-template-columns:1fr;}
      .sidebar{display:none;}
      .main{padding:24px 20px;}
      .toc ol{grid-template-columns:1fr;}
      .cover{margin:-24px -20px 32px;padding:36px 20px;}
    }
  </style>
</head>
<body>
<div class="manual-wrap">

  <!-- Sidebar -->
  <nav class="sidebar">
    <div class="sb-logo">
      <h1>PUP Maragondon Library</h1>
      <p>Admin User Manual</p>
    </div>

    <div class="sb-section">Getting Started</div>
    <a href="#login" class="sb-link"><i class="fa-solid fa-right-to-bracket"></i> Logging In &amp; Out</a>
    <a href="#dashboard" class="sb-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>

    <div class="sb-section">Content Management</div>
    <a href="#arrivals" class="sb-link"><i class="fa-solid fa-star"></i> New Arrivals</a>
    <a href="#personnel" class="sb-link"><i class="fa-solid fa-users"></i> Personnel</a>
    <a href="#programs" class="sb-link"><i class="fa-solid fa-calendar-days"></i> Programs &amp; Events</a>
    <a href="#services" class="sb-link"><i class="fa-solid fa-hand-holding-heart"></i> Library Services</a>
    <a href="#holdings" class="sb-link"><i class="fa-solid fa-book"></i> Library Holdings</a>
    <a href="#resources" class="sb-link"><i class="fa-solid fa-database"></i> Online Resources</a>
    <a href="#featured" class="sb-link"><i class="fa-solid fa-bookmark"></i> Featured Resource</a>
    <a href="#about" class="sb-link"><i class="fa-solid fa-circle-info"></i> About Page</a>
    <a href="#linkages" class="sb-link"><i class="fa-solid fa-link"></i> Linkages</a>
    <a href="#guidelines" class="sb-link"><i class="fa-solid fa-gavel"></i> Guidelines</a>
    <a href="#media" class="sb-link"><i class="fa-solid fa-photo-film"></i> Media Library</a>

    <div class="sb-section">Configuration</div>
    <a href="#settings" class="sb-link"><i class="fa-solid fa-sliders"></i> Site Settings</a>
    <a href="#password" class="sb-link"><i class="fa-solid fa-key"></i> Change Password</a>

    <div class="sb-section">Help</div>
    <a href="#tips" class="sb-link"><i class="fa-solid fa-lightbulb"></i> Tips &amp; Reminders</a>
    <a href="#troubleshoot" class="sb-link"><i class="fa-solid fa-wrench"></i> Troubleshooting</a>

    <div class="sb-print" onclick="downloadPDF()" id="pdf-btn">
      <i class="fa-solid fa-file-pdf"></i> Download PDF
    </div>
    <div class="sb-print" onclick="window.print()" style="margin-top:0;">
      <i class="fa-solid fa-print"></i> Print
    </div>

    <div style="margin:12px 20px 0;text-align:center;">
      <a href="index.php" style="font-size:0.75rem;color:#666;">← Back to Admin Panel</a>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="main">

    <!-- Cover -->
    <div class="cover">
      <h1><i class="fa-solid fa-book-open" style="opacity:0.7;margin-right:12px;"></i>Admin User Manual</h1>
      <p>PUP Maragondon Campus Library — Content Management System</p>
      <div class="cover-meta">
        <div class="cover-meta-item"><strong>Version 1.0</strong>2025</div>
        <div class="cover-meta-item"><strong>For</strong>Library Admin Staff</div>
        <div class="cover-meta-item"><strong>Access</strong>yoursite.com/admin/</div>
      </div>
    </div>

    <!-- TOC -->
    <div class="toc">
      <h3>Table of Contents</h3>
      <ol>
        <li><a href="#login">Logging In &amp; Out</a></li>
        <li><a href="#dashboard">Dashboard</a></li>
        <li><a href="#arrivals">New Arrivals</a></li>
        <li><a href="#personnel">Personnel</a></li>
        <li><a href="#programs">Programs &amp; Events</a></li>
        <li><a href="#services">Library Services</a></li>
        <li><a href="#holdings">Library Holdings</a></li>
        <li><a href="#resources">Online Resources</a></li>
        <li><a href="#featured">Featured Resource</a></li>
        <li><a href="#about">About Page</a></li>
        <li><a href="#linkages">Linkages</a></li>
        <li><a href="#guidelines">Guidelines</a></li>
        <li><a href="#media">Media Library</a></li>
        <li><a href="#settings">Site Settings</a></li>
        <li><a href="#password">Change Password</a></li>
        <li><a href="#tips">Tips &amp; Reminders</a></li>
        <li><a href="#troubleshoot">Troubleshooting</a></li>
      </ol>
    </div>

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="login"><i class="fa-solid fa-right-to-bracket"></i> 1. Logging In &amp; Out</h2>

    <div class="lead">The admin panel is the private area where you manage all the content on the library website. Only you and authorized staff can access it.</div>

    <h3 class="sub-title">How to Log In</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Open your web browser</strong><span>Use Google Chrome, Microsoft Edge, or Firefox for best results.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Go to the admin login page</strong><span>Type your website address followed by <code>/admin/</code> in the address bar. Example: <code>https://yoursite.com/admin/</code></span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Enter your username and password</strong><span>Type your credentials in the fields provided. The default is <strong>admin</strong> for username and <strong>admin123</strong> for the password — but you should change this after your first login.</span></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Click "Sign In"</strong><span>You will be taken to the Dashboard if your credentials are correct.</span></div></div>
    </div>

    <div class="warn"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>Important:</strong> After your first login, immediately go to <strong>Settings → Change Password</strong> and set a strong personal password. Do not leave the default password active on a live website.</p></div>

    <h3 class="sub-title">How to Log Out</h3>
    <p>Always log out when you are done, especially if you are using a shared or public computer.</p>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Look at the top-right corner of the admin panel</strong><span>You will see your username or a logout button.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Click "Logout"</strong><span>You will be redirected back to the login page. Your session is now safely closed.</span></div></div>
    </div>

    <div class="note"><i class="fa-solid fa-circle-info"></i><p>For security, the system will automatically log you out after <strong>2 hours</strong> of inactivity. If this happens, simply log in again.</p></div>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="dashboard"><i class="fa-solid fa-gauge"></i> 2. Dashboard</h2>

    <div class="lead">The Dashboard is the first page you see after logging in. It gives you a quick overview of your website content and recent activity.</div>

    <h3 class="sub-title">What You Will See</h3>
    <table class="fields-table">
      <thead><tr><th>Section</th><th>What It Shows</th></tr></thead>
      <tbody>
        <tr><td><strong>Stat Cards</strong></td><td>A row of colored cards showing counts — how many New Arrivals, Programs, Online Resources, Personnel, Holdings Categories, and whether a Featured resource is set. Click any card to go directly to that section.</td></tr>
        <tr><td><strong>Quick Actions</strong></td><td>Shortcut buttons for the most common tasks: Add New Arrival, Update Featured Resource, Add Program, Add Personnel, Edit Settings.</td></tr>
        <tr><td><strong>Recent File Changes</strong></td><td>A table showing which parts of the website were most recently updated and when. Useful for tracking what was last changed.</td></tr>
        <tr><td><strong>View Public Site button</strong></td><td>Located at the top right. Click it to open the live public website in a new tab so you can see your changes.</td></tr>
      </tbody>
    </table>

    <div class="tip"><i class="fa-solid fa-lightbulb"></i><p><strong>Tip:</strong> After making any change in the admin panel, click <strong>"View Public Site"</strong> (top-right of dashboard) to check how it looks on the actual website.</p></div>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="arrivals"><i class="fa-solid fa-star"></i> 3. New Arrivals</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>New Arrivals</strong></div>
    <div class="lead">This section controls the "New Arrivals" cards shown on the homepage. Each card represents a recently added book, thesis, journal, or other library material.</div>

    <h3 class="sub-title">How to Add a New Arrival</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Click "Add Arrival"</strong><span>The button is in the top-right corner of the New Arrivals page. A form panel will slide open.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Fill in the details</strong><span>See the field descriptions below.</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Upload or select a cover image</strong><span>Either drag and drop a photo into the upload box, or click "Browse" to pick one from the Media Library.</span></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Click "Add Arrival" to save</strong><span>The item will appear in the list and on the public website immediately.</span></div></div>
    </div>

    <h3 class="sub-title">Fields</h3>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Required?</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Type</strong></td><td><span class="req">Required</span></td><td>Choose what kind of material this is: Book, E-Book, Thesis, Module, Video, Journal, Magazine, or Other.</td></tr>
        <tr><td><strong>Title</strong></td><td><span class="req">Required</span></td><td>The full title of the book, thesis, or material.</td></tr>
        <tr><td><strong>Author / Creator</strong></td><td><span class="opt">Optional</span></td><td>The author's name. Leave blank if not applicable.</td></tr>
        <tr><td><strong>Date Added</strong></td><td><span class="opt">Optional</span></td><td>The date this material was acquired or added. Example: 2025-01-15</td></tr>
        <tr><td><strong>Cover Image</strong></td><td><span class="opt">Optional</span></td><td>A photo of the book cover or a related image. JPG or PNG, max 5 MB.</td></tr>
      </tbody>
    </table>

    <h3 class="sub-title">How to Edit an Existing Arrival</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Find the item in the list</strong><span>All arrivals are shown in a table on this page.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Click the pencil (✏️) icon</strong><span>The form will open with the current information already filled in.</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Make your changes and click "Save Changes"</strong></div></div>
    </div>

    <h3 class="sub-title">How to Delete an Arrival</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Click the red trash (🗑️) icon</strong><span>Located in the Actions column for each item.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Confirm the deletion</strong><span>A confirmation box will appear. Click OK to permanently delete the item.</span></div></div>
    </div>

    <div class="warn"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>Warning:</strong> Deleting an arrival is permanent. There is no undo button. Make sure you really want to remove it before confirming.</p></div>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="personnel"><i class="fa-solid fa-users"></i> 4. Personnel</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Personnel</strong></div>
    <div class="lead">This section manages the library staff cards shown on the "Library Personnel" (Administration) page of the public website.</div>

    <h3 class="sub-title">How to Add a Staff Member</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Click "Add Personnel"</strong><span>The button is at the top-right of the page.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Fill in the staff member's information</strong><span>See the field descriptions in the table below.</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Upload a profile photo</strong><span>Click the photo upload box or use "Browse" to pick from the Media Library. A square photo works best (like a 1x1 inch ID photo format).</span></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Click "Add Staff"</strong></div></div>
    </div>

    <h3 class="sub-title">Fields</h3>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Required?</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Full Name</strong></td><td><span class="req">Required</span></td><td>The staff member's complete name. Example: Maria Santos</td></tr>
        <tr><td><strong>Role / Position</strong></td><td><span class="opt">Optional</span></td><td>Their job title. Example: University Librarian, Library Assistant</td></tr>
        <tr><td><strong>Bio / Short Description</strong></td><td><span class="opt">Optional</span></td><td>A 1–2 sentence description shown on their card. If left blank, a default text is used.</td></tr>
        <tr><td><strong>Specialization</strong></td><td><span class="opt">Optional</span></td><td>Their area of expertise. Example: Reference Services, Cataloging</td></tr>
        <tr><td><strong>Department / Section</strong></td><td><span class="opt">Optional</span></td><td>Which section they belong to. Example: Circulation Section, Technical Services</td></tr>
        <tr><td><strong>Email</strong></td><td><span class="opt">Optional</span></td><td>Their work email address. Will appear as a clickable link on their card.</td></tr>
        <tr><td><strong>Extension</strong></td><td><span class="opt">Optional</span></td><td>Their office phone extension number.</td></tr>
        <tr><td><strong>Profile Photo</strong></td><td><span class="opt">Optional</span></td><td>If no photo is uploaded, their initials will be shown in a circle placeholder instead.</td></tr>
      </tbody>
    </table>

    <div class="tip"><i class="fa-solid fa-lightbulb"></i><p><strong>Tip:</strong> Staff cards are displayed in the same order they were added. The first person added appears first on the page.</p></div>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="programs"><i class="fa-solid fa-calendar-days"></i> 5. Programs &amp; Events</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Programs</strong></div>
    <div class="lead">This section manages the "Programs, Events & Activities" page. Each entry represents one library program such as Library Orientation, National Book Week, or other campus events.</div>

    <h3 class="sub-title">How to Add a Program</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Click "Add Program"</strong><span>Button at the top-right of the page.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Fill in the program details</strong></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Upload event photos (optional)</strong><span>You can upload multiple photos at once. These appear as a photo gallery on the program page.</span></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Click "Add Program"</strong></div></div>
    </div>

    <h3 class="sub-title">Fields</h3>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Required?</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Title</strong></td><td><span class="req">Required</span></td><td>The name of the program. Example: Library Orientation 2025</td></tr>
        <tr><td><strong>Label / Tag</strong></td><td><span class="opt">Optional</span></td><td>A short category label shown above the title. Example: Annual Program, Ongoing, Special Event</td></tr>
        <tr><td><strong>Description</strong></td><td><span class="opt">Optional</span></td><td>A paragraph explaining what this program is about.</td></tr>
        <tr><td><strong>When is it Held?</strong></td><td><span class="opt">Optional</span></td><td>Describe the schedule. Example: Every start of the school year, held in June.</td></tr>
        <tr><td><strong>Who Should Attend?</strong></td><td><span class="opt">Optional</span></td><td>Describe the target participants. Example: All incoming freshmen and transferees.</td></tr>
        <tr><td><strong>What's Covered?</strong></td><td><span class="opt">Optional</span></td><td>List what will be discussed or done. Example: Library tour, OPAC tutorial, borrowing rules.</td></tr>
        <tr><td><strong>Contact / Get in Touch</strong></td><td><span class="opt">Optional</span></td><td>Contact information for inquiries. Example: Email the librarian at library@pup.edu.ph</td></tr>
        <tr><td><strong>Photos</strong></td><td><span class="opt">Optional</span></td><td>Upload one or more event photos. These form a clickable photo gallery.</td></tr>
      </tbody>
    </table>

    <h3 class="sub-title">Removing a Photo from a Program</h3>
    <p>When editing a program, existing photos are shown with a small checkbox labeled "Remove." Check the box next to any photo you want to delete, then click Save.</p>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="services"><i class="fa-solid fa-hand-holding-heart"></i> 6. Library Services</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Services</strong></div>
    <div class="lead">This section manages the "Library Services" page. Each entry is a tab on that page (for example: Circulation, Reference, Special Collections). Each tab has a title, description, image, and a list of bullet-point items.</div>

    <h3 class="sub-title">How to Add a Service Tab</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Click "Add Service"</strong></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Fill in the service information</strong><span>See the fields table below.</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Add service items</strong><span>Click "Add Item" to add each bullet point under this service. Each item has a title and a short description.</span></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Upload a service photo</strong><span>Optional. This image appears alongside the service description.</span></div></div>
      <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Click "Save"</strong></div></div>
    </div>

    <h3 class="sub-title">Fields</h3>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Required?</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Tab ID</strong></td><td><span class="req">Required</span></td><td>A short code used internally. Use lowercase letters and hyphens only. Example: <code>circulation</code>, <code>reference</code>. No spaces allowed.</td></tr>
        <tr><td><strong>Title</strong></td><td><span class="req">Required</span></td><td>The name shown on the tab button. Example: Circulation Services</td></tr>
        <tr><td><strong>Icon class</strong></td><td><span class="opt">Optional</span></td><td>A Font Awesome icon code. Example: <code>fa-solid fa-book</code>. You can leave this blank.</td></tr>
        <tr><td><strong>Description</strong></td><td><span class="opt">Optional</span></td><td>An introductory paragraph for this service.</td></tr>
        <tr><td><strong>Image</strong></td><td><span class="opt">Optional</span></td><td>A photo related to this service.</td></tr>
        <tr><td><strong>Service Items</strong></td><td><span class="opt">Optional</span></td><td>Individual bullet points. Each item has a title and description. Click "Add Item" to add more, or the X button to remove one.</td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="holdings"><i class="fa-solid fa-book"></i> 7. Library Holdings</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Holdings</strong></div>
    <div class="lead">This section manages the "Library Holdings" page — the numbers shown in the statistics strip and the collection category cards below it.</div>

    <h3 class="sub-title">Statistics Strip</h3>
    <p>The statistics strip shows animated counting numbers at the top of the Holdings page (e.g., "8,500 Book Titles"). You can update these numbers here.</p>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Target Number</strong></td><td>The final number the animation counts up to. Example: 8500</td></tr>
        <tr><td><strong>Suffix</strong></td><td>Text added after the number. Example: <code>+</code> makes it show "8500+". Leave blank for no suffix.</td></tr>
        <tr><td><strong>Label</strong></td><td>The text below the number. Example: Book Titles</td></tr>
      </tbody>
    </table>
    <p>Click <strong>"Add Stat"</strong> to add a new counter, or the trash icon to remove one. Click <strong>"Save"</strong> when done.</p>

    <h3 class="sub-title">Holdings Categories</h3>
    <p>These are the collection cards shown further down the Holdings page (e.g., Filipiniana, Theses, Periodicals). Each card shows a number, icon, title, and description.</p>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Number</strong></td><td>The collection size number shown on the card. Example: 3,500</td></tr>
        <tr><td><strong>Icon class</strong></td><td>A Font Awesome icon. Example: <code>fa-solid fa-graduation-cap</code></td></tr>
        <tr><td><strong>Title</strong></td><td>The collection name. Example: Filipiniana Collection</td></tr>
        <tr><td><strong>Description</strong></td><td>A short description of what this collection contains.</td></tr>
        <tr><td><strong>Tag</strong></td><td>A category label shown as a small badge. Example: Local, Academic, Reference</td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="resources"><i class="fa-solid fa-database"></i> 8. Online Resources</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Resources</strong></div>
    <div class="lead">This section manages the list of online databases and e-resources shown on the "Online Resources" page. Each entry is one database or platform the library provides access to.</div>

    <h3 class="sub-title">How to Add a Resource</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Click "Add Resource"</strong></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Fill in the details</strong></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Click "Save Resource"</strong></div></div>
    </div>

    <h3 class="sub-title">Fields</h3>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Required?</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Name</strong></td><td><span class="req">Required</span></td><td>The name of the database or platform. Example: JSTOR, iG Library</td></tr>
        <tr><td><strong>Description</strong></td><td><span class="opt">Optional</span></td><td>A brief explanation of what this resource provides.</td></tr>
        <tr><td><strong>URL / Link</strong></td><td><span class="opt">Optional</span></td><td>The website address users will be sent to when they click this resource. Must start with <code>https://</code></td></tr>
        <tr><td><strong>Category</strong></td><td><span class="opt">Optional</span></td><td>Choose from: Academic, E-Books, News &amp; Periodicals, Government, or Other. Used to group resources on the page.</td></tr>
        <tr><td><strong>Logo</strong></td><td><span class="opt">Optional</span></td><td>Upload the logo/icon image of the database. Square images work best.</td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="featured"><i class="fa-solid fa-bookmark"></i> 9. Featured Resource</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Featured</strong></div>
    <div class="lead">The Featured Resource is a highlighted spotlight shown on the homepage. It highlights one special resource, book, or collection that you want to promote at any given time. There is only one featured item — it replaces the previous one every time you save.</div>

    <h3 class="sub-title">How to Update the Featured Resource</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Go to Featured in the admin menu</strong></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Fill in or update the fields</strong><span>All current information is already loaded in the form.</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Click "Update Featured Resource"</strong></div></div>
    </div>

    <h3 class="sub-title">Fields</h3>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Required?</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Title</strong></td><td><span class="req">Required</span></td><td>The name of the featured resource.</td></tr>
        <tr><td><strong>Description</strong></td><td><span class="opt">Optional</span></td><td>A few sentences describing the resource and why it's notable.</td></tr>
        <tr><td><strong>Link / URL</strong></td><td><span class="opt">Optional</span></td><td>A link where users can access the resource. Use <code>#</code> if there is no direct link.</td></tr>
        <tr><td><strong>Image</strong></td><td><span class="opt">Optional</span></td><td>A cover photo or thumbnail image for the resource.</td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="about"><i class="fa-solid fa-circle-info"></i> 10. About Page</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>About</strong></div>
    <div class="lead">The About page has three sections you can manage: the VGMO (Vision, Goals, Mission &amp; Objectives) cards, the Library Hours schedule, and the History Timeline.</div>

    <h3 class="sub-title">Section A: Vision, Mission, Goals &amp; Objectives (VGMO)</h3>
    <p>These are four content cards shown on the About page. Edit the text in each box and click <strong>"Save VGMO"</strong>.</p>
    <table class="fields-table">
      <thead><tr><th>Card</th><th>What to Write</th></tr></thead>
      <tbody>
        <tr><td><strong>Mission Statement</strong></td><td>A paragraph describing the library's mission — what it does and why.</td></tr>
        <tr><td><strong>Our Vision</strong></td><td>A paragraph describing the library's vision for the future.</td></tr>
        <tr><td><strong>Goals</strong></td><td>A list of goals. Each line is one goal. Click "Add goal" to add more. Click the X button to remove one.</td></tr>
        <tr><td><strong>Objectives</strong></td><td>A list of objectives. Same format as Goals.</td></tr>
      </tbody>
    </table>

    <h3 class="sub-title">Section B: Library Hours</h3>
    <p>This controls the weekly schedule shown on the About page AND the "Open Today / Closed Today" status shown on the homepage. Update this whenever the schedule changes.</p>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Scroll down to "Library Hours"</strong></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>For each day, check the "Open" checkbox if the library is open that day</strong><span>Leave it unchecked for days the library is closed (e.g., Sunday).</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Enter the hours in the Time field</strong><span>Example: <code>8:00 AM – 5:00 PM</code>. If closed, you can type "Closed" or leave it blank.</span></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Update the Notice/Note field if needed</strong><span>This is optional text shown above the schedule. Example: "No Noon Break" or "Extended hours during finals week."</span></div></div>
      <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Click "Save Hours"</strong></div></div>
    </div>
    <div class="note"><i class="fa-solid fa-circle-info"></i><p>The homepage hero card automatically shows "Open Today" or "Closed Today" based on what you set here. For example, if today is Saturday and Saturday is unchecked, it will say "Closed Today" on the homepage.</p></div>

    <h3 class="sub-title">Section C: History Timeline</h3>
    <p>This is a list of important dates and milestones in the library's history, shown on the About page.</p>

    <h3 class="sub-title">How to Add a Timeline Event</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Click "Add Event"</strong><span>Button at the top-right of the Timeline section.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Fill in the Year, Title, Short Description, and Expanded Detail</strong></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Click "Save Event"</strong></div></div>
    </div>

    <table class="fields-table">
      <thead><tr><th>Field</th><th>Required?</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Year</strong></td><td><span class="req">Required</span></td><td>The year the event happened. Example: 1987</td></tr>
        <tr><td><strong>Title</strong></td><td><span class="req">Required</span></td><td>A short name for the milestone. Example: Library Established</td></tr>
        <tr><td><strong>Short Description</strong></td><td><span class="opt">Optional</span></td><td>A brief summary shown directly on the timeline card.</td></tr>
        <tr><td><strong>Expanded Detail</strong></td><td><span class="opt">Optional</span></td><td>A longer explanation shown when a visitor clicks on the timeline card to read more.</td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="linkages"><i class="fa-solid fa-link"></i> 11. Linkages</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Linkages</strong></div>
    <div class="lead">The Linkages page shows the library's partner organizations and memoranda of agreement (MOUs). This section has two parts: the text content at the top, and the partner cards below.</div>

    <h3 class="sub-title">Text Content</h3>
    <p>Edit the introductory paragraph, MOU section title and description, the Call-to-Action (CTA) title and description, and the contact email. Click <strong>"Save Text"</strong> when done.</p>

    <h3 class="sub-title">Partner Cards</h3>
    <p>Each card represents one partner organization. Add, edit, or delete cards using the <strong>"Add Card"</strong> button and the action buttons in the list.</p>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Title</strong></td><td>The partner organization's name.</td></tr>
        <tr><td><strong>Icon class</strong></td><td>A Font Awesome icon. Example: <code>fa-solid fa-university</code></td></tr>
        <tr><td><strong>Description</strong></td><td>A short description of the partnership.</td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="guidelines"><i class="fa-solid fa-gavel"></i> 12. Guidelines</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Guidelines</strong></div>
    <div class="lead">This section manages the Library Guidelines page — the rules and policies shown to library users. Each entry is one accordion section (a collapsible panel with a title and a list of rules).</div>

    <h3 class="sub-title">How to Add a Guidelines Section</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Click "Add Rule Group"</strong></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Enter the Title</strong><span>Example: Borrowing Policies, Internet Use Rules</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Enter an Intro paragraph</strong><span>Optional. A brief sentence before the list of rules.</span></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Add each rule</strong><span>Click "Add item" to add a new rule line. Type the rule text and click Add. Repeat for each rule.</span></div></div>
      <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Check "Open by default"</strong><span>Optional. If checked, this section will be expanded (visible) when the page loads. Leave unchecked for collapsed.</span></div></div>
      <div class="step"><div class="step-num">6</div><div class="step-body"><strong>Click "Save"</strong></div></div>
    </div>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="media"><i class="fa-solid fa-photo-film"></i> 13. Media Library</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Media</strong></div>
    <div class="lead">The Media Library is a central storage area for all images and videos you upload to the website. Instead of uploading the same photo multiple times, you upload it once here and then reuse it anywhere.</div>

    <h3 class="sub-title">How to Upload a New File</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Go to Media in the admin menu</strong></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Drag and drop your image into the upload area</strong><span>Or click the upload box to browse your computer files.</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Wait for the upload to finish</strong><span>A green checkmark or success message will appear. The file will now appear in the grid below.</span></div></div>
    </div>

    <h3 class="sub-title">How to Use a Media Library Image in Other Sections</h3>
    <p>In most sections (Personnel, Programs, Services, etc.), photo fields have a <strong>"Browse"</strong> button next to them. Clicking it opens a popup showing all your uploaded files. Click any image to select it — the URL is automatically filled in for you.</p>

    <h3 class="sub-title">How to Delete a File</h3>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Find the image in the Media Library grid</strong></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Click the red trash icon on the image</strong></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Confirm deletion</strong></div></div>
    </div>

    <div class="warn"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>Warning:</strong> If you delete an image that is currently being used on the website (for example, a staff photo), that image will disappear from the public page. Make sure the image is no longer needed before deleting it.</p></div>

    <h3 class="sub-title">File Size and Type Limits</h3>
    <table class="fields-table">
      <thead><tr><th>Type</th><th>Allowed Formats</th><th>Maximum Size</th></tr></thead>
      <tbody>
        <tr><td><strong>Images</strong></td><td>JPG, JPEG, PNG, GIF, WEBP</td><td>5 MB per file</td></tr>
        <tr><td><strong>Videos</strong></td><td>MP4, WEBM, OGV, MOV</td><td>20 MB per file</td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="settings"><i class="fa-solid fa-sliders"></i> 14. Site Settings</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Settings</strong></div>
    <div class="lead">Site Settings lets you control the overall information of the website — the site name, contact details, social media links, homepage statistics, and the YouTube video for the virtual tour.</div>

    <h3 class="sub-title">General &amp; Contact</h3>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Site Title</strong></td><td>The name shown in the browser tab. Example: PUP Maragondon – Digital Library</td></tr>
        <tr><td><strong>Hero Tagline</strong></td><td>The short paragraph shown below "Discover, Learn, and Innovate" in the homepage hero section.</td></tr>
        <tr><td><strong>AVP YouTube Video ID</strong></td><td>The code from a YouTube video URL. If your video link is <code>https://youtube.com/watch?v=<strong>T53v0NejzI4</strong></code>, then the ID is <code>T53v0NejzI4</code>. This controls both the thumbnail image and the video that plays when users click "Watch Full Tour."</td></tr>
        <tr><td><strong>Survey URL</strong></td><td>The full URL of your Google Form or survey. Example: <code>https://forms.gle/abc123</code></td></tr>
        <tr><td><strong>OPAC URL</strong></td><td>The full URL of your library catalog. Example: <code>https://ils.pup.edu.ph/</code></td></tr>
        <tr><td><strong>Address</strong></td><td>The library's physical address. Shown on the Administration page contact banner.</td></tr>
        <tr><td><strong>Office Hours</strong></td><td>General hours text shown on the contact banner. Example: Mon – Fri, 8:00 AM – 5:00 PM</td></tr>
        <tr><td><strong>Email</strong></td><td>The library's official email address.</td></tr>
        <tr><td><strong>Phone</strong></td><td>The library's phone number.</td></tr>
        <tr><td><strong>Facebook / YouTube / Twitter URL</strong></td><td>Full URLs to the library's social media pages. Leave blank if not applicable.</td></tr>
      </tbody>
    </table>
    <p>Click <strong>"Save General Settings"</strong> when done.</p>

    <h3 class="sub-title">Hero Stats</h3>
    <p>These are the 2–3 statistics shown inside the info card on the homepage hero section (e.g., "8,500+ Books").</p>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Number / Value</strong></td><td>The value to display. Example: <code>8,500+</code></td></tr>
        <tr><td><strong>Label</strong></td><td>The description below the number. Example: <code>Books</code></td></tr>
      </tbody>
    </table>
    <p>Click <strong>"Add Stat"</strong> to add a new item. Click the trash icon to remove one. Only the first 3 items are shown on the homepage. Click <strong>"Save"</strong> when done.</p>

    <h3 class="sub-title">Animated Counters</h3>
    <p>These are the large animated counting numbers shown in the statistics strip section of the homepage (separate from the hero card stats).</p>
    <table class="fields-table">
      <thead><tr><th>Field</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><strong>Target Number</strong></td><td>The number the animation counts up to. Use whole numbers only. Example: 8500</td></tr>
        <tr><td><strong>Suffix</strong></td><td>Text added right after the number. Example: <code>+</code> gives "8500+". Leave blank for no suffix.</td></tr>
        <tr><td><strong>Label</strong></td><td>The description below the number. Example: Book Titles</td></tr>
      </tbody>
    </table>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="password"><i class="fa-solid fa-key"></i> 15. Change Password</h2>

    <div class="menu-path"><i class="fa-solid fa-chevron-right"></i> Admin Menu → <strong>Settings</strong> → scroll down to Security</div>
    <div class="lead">Changing your password regularly is a good security habit. It is strongly recommended to change the default password the first time you log in.</div>

    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Go to Settings and scroll down to "Security — Change Password"</strong></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Type your Current Password</strong></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Type your New Password</strong><span>Must be at least 8 characters long. Use a mix of letters, numbers, and symbols for better security.</span></div></div>
      <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Type the new password again in "Confirm New Password"</strong><span>Both fields must match exactly.</span></div></div>
      <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Click "Change Password"</strong></div></div>
    </div>

    <div class="warn"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>Write down your new password and store it somewhere safe.</strong> If you forget it, the password cannot be recovered through the website — you would need technical assistance to reset it in the server files.</p></div>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="tips"><i class="fa-solid fa-lightbulb"></i> 16. Tips &amp; Reminders</h2>

    <div class="note"><i class="fa-solid fa-circle-info"></i><p><strong>Always check the public site after saving.</strong> Click "View Public Site" on the dashboard to confirm your changes look correct before closing the admin panel.</p></div>

    <div class="tip"><i class="fa-solid fa-lightbulb"></i><p><strong>Use the Media Library for reusable images.</strong> Instead of uploading the same logo or photo multiple times in different sections, upload it once to the Media Library, then use the "Browse" button to reuse it.</p></div>

    <div class="tip"><i class="fa-solid fa-lightbulb"></i><p><strong>Image sizes that work well:</strong> Staff photos — square (equal width and height). Book covers — portrait (taller than wide). Event photos — landscape (wider than tall). Logos — square.</p></div>

    <div class="note"><i class="fa-solid fa-circle-info"></i><p><strong>The order of items matters.</strong> For New Arrivals, Programs, Resources, and Personnel, items are displayed in the order they appear in the list. The item at the top of the admin list appears first on the website.</p></div>

    <div class="warn"><i class="fa-solid fa-triangle-exclamation"></i><p><strong>Do not share your admin password.</strong> Only authorized staff should have access to the admin panel. If someone else needs access, create awareness that sharing credentials is a security risk.</p></div>

    <div class="tip"><i class="fa-solid fa-lightbulb"></i><p><strong>Log out when finished.</strong> Especially important if you are using a shared computer in the library or office.</p></div>

    <div class="note"><i class="fa-solid fa-circle-info"></i><p><strong>Red asterisk (*) means required.</strong> Any field marked with a red star cannot be left empty. The form will not save if required fields are blank.</p></div>

    <hr class="divider">

    <!-- ════════════════════════════════════════════ -->
    <h2 class="sec-title" id="troubleshoot"><i class="fa-solid fa-wrench"></i> 17. Troubleshooting</h2>

    <h3 class="sub-title">I saved changes but nothing changed on the public website</h3>
    <p>Try these steps:</p>
    <div class="steps">
      <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Hard-refresh the public page</strong><span>Press <strong>Ctrl + Shift + R</strong> (Windows) or <strong>Cmd + Shift + R</strong> (Mac) to force the browser to reload the latest version.</span></div></div>
      <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Try a different browser or incognito/private window</strong><span>Sometimes old data is stored in your browser's cache.</span></div></div>
      <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Confirm the save was successful</strong><span>Look for the green success message ("Saved successfully") at the top of the admin page after saving. If you see a red error message, read it carefully — it will tell you what went wrong.</span></div></div>
    </div>

    <h3 class="sub-title">I cannot log in — it says "Too many failed attempts"</h3>
    <p>For security, the system locks out login after 5 wrong password attempts. <strong>Wait 15 minutes</strong> and try again. If you have forgotten your password, contact your website administrator for a password reset.</p>

    <h3 class="sub-title">My image upload failed</h3>
    <p>Check the following:</p>
    <ul style="padding-left:20px;margin-bottom:16px;display:flex;flex-direction:column;gap:8px;">
      <li style="font-size:0.9rem;color:#333;">The file must be JPG, PNG, GIF, or WEBP (for images).</li>
      <li style="font-size:0.9rem;color:#333;">The file size must be under <strong>5 MB</strong> for images, or <strong>20 MB</strong> for videos.</li>
      <li style="font-size:0.9rem;color:#333;">Make sure you have a stable internet connection when uploading.</li>
    </ul>

    <h3 class="sub-title">A staff photo or program image is not showing on the public website</h3>
    <p>The image may have been deleted from the Media Library. Go to Media, check if the file is still there. If it was deleted, re-upload the image and re-assign it to the staff member or program by editing their record and selecting the new image.</p>

    <h3 class="sub-title">I accidentally deleted something</h3>
    <p>Unfortunately, there is no undo for deletions. Data that is deleted is permanently removed. You will need to re-enter the information manually. This is why it is important to double-check before confirming any deletion.</p>

    <h3 class="sub-title">The page is blank or shows an error after saving Settings</h3>
    <p>Go back using your browser's Back button, or navigate directly to <code>yoursite.com/admin/settings.php</code>. If the error persists, contact your web administrator.</p>

    <hr class="divider">

    <div style="text-align:center;padding:32px 0 16px;color:#999;font-size:0.8rem;">
      <i class="fa-solid fa-book-open" style="color:#800000;margin-right:6px;"></i>
      PUP Maragondon Campus Library — Admin Manual v1.0 &nbsp;·&nbsp;
      For technical support, contact your website developer.
    </div>

  </main>
</div>

<!-- Loading overlay shown while PDF is generating -->
<div id="pdf-overlay">
  <div class="pdf-spinner"></div>
  <p>Generating PDF, please wait…</p>
</div>

<script>
// ── Highlight active sidebar link on scroll ──────────────────────
const sections = document.querySelectorAll('h2.sec-title[id]');
const links = document.querySelectorAll('.sb-link');
window.addEventListener('scroll', () => {
  let current = '';
  sections.forEach(s => { if (window.scrollY >= s.offsetTop - 80) current = s.id; });
  links.forEach(l => {
    l.style.background = '';
    l.style.color = '';
    if (l.getAttribute('href') === '#' + current) {
      l.style.background = 'rgba(201,168,76,0.15)';
      l.style.color = '#c9a84c';
    }
  });
});

// ── PDF Download ─────────────────────────────────────────────────
async function downloadPDF() {
  const overlay = document.getElementById('pdf-overlay');
  const wrap    = document.querySelector('.manual-wrap');
  const main    = document.querySelector('.main');
  const sidebar = document.querySelector('.sidebar');

  // Show loading overlay
  overlay.classList.add('show');

  // Temporarily collapse layout so only main content is captured
  const prevWrapStyle   = wrap.style.cssText;
  const prevMainStyle   = main.style.cssText;
  const prevSidebarDisp = sidebar.style.display;

  sidebar.style.display = 'none';
  wrap.style.display    = 'block';
  main.style.padding    = '20px';
  main.style.maxWidth   = '100%';

  // Small delay to let the DOM repaint before capture
  await new Promise(r => setTimeout(r, 120));

  const opt = {
    margin:     [12, 12, 12, 12],
    filename:   'PUP-Maragondon-Library-Admin-Manual.pdf',
    image:      { type: 'jpeg', quality: 0.97 },
    html2canvas: {
      scale:      2,
      useCORS:    true,
      logging:    false,
      letterRendering: true
    },
    jsPDF: {
      unit:        'mm',
      format:      'a4',
      orientation: 'portrait'
    },
    pagebreak: { mode: ['avoid-all', 'css'], before: '.divider' }
  };

  try {
    await html2pdf().set(opt).from(main).save();
  } catch (e) {
    alert('PDF generation failed. Please try the Print button instead.');
  }

  // Restore layout
  sidebar.style.display = prevSidebarDisp;
  wrap.style.cssText    = prevWrapStyle;
  main.style.cssText    = prevMainStyle;

  overlay.classList.remove('show');
}
</script>
</body>
</html>
