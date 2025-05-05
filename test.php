<?php
session_start();
require 'config.php';

// 1. Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// 2. Get user data
$stmt = $conn->prepare("SELECT username, role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: signin.php");
    exit();
}

// 3. Set variables
$username = htmlspecialchars($user['username']);
$role = $user['role'];

// 4. Determine role display text
$role_display = match($role) {
    'both'       => 'Artist and Enthusiast',
    'artist'     => 'Artist',
    'enthusiast' => 'Enthusiast',
    default      => 'Member'
};
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enthusiast Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>

    :root { 
    --primary-light: #a4e0dd;
    --primary: #78cac5;
    --primary-dark: #4db8b2;
    --secondary-light: #f2e6b5;
    --secondary: #e7cf9b;
    --secondary-dark: #96833f;
    --light: #EEF9FF;
    --dark: #173836;
}

body {
    background-color: var(--light);
    font-family: 'Nunito', sans-serif;

}

.profile-header {
    height: 300px;
    background-image: linear-gradient(45deg, 
        rgba(77, 184, 178, 0.8), 
        rgba(164, 224, 221, 0.8)),
        url('default-bg.jpg');
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    position: relative;
    border-radius: 0% 0% 30% 30%;
    overflow: hidden;
    transition-property: background-image;
    transition-duration: 0.3s;
    transition-timing-function: ease;
    cursor: pointer;
}

.profile-image-container {
    position: relative;
    width: 150px;
    height: 150px;
    margin-top: -75px;
    margin-right: auto;
    margin-bottom: 1rem;
    margin-left: auto;
    cursor: pointer;
    transition-property: transform;
    transition-duration: 0.3s;
    transition-timing-function: ease;
}

.profile-image {
    width: 100%;
    height: 100%;
    border-width: 4px;
    border-style: solid;
    border-color: var(--light);
    border-radius: 50%;
    object-fit: cover;
    transition-property: all;
    transition-duration: 0.3s;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
}

.profile-image-container:hover .profile-image {
    transform: scale(1.05);
    box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.2);
}

.edit-overlay {
    position: absolute;
    top: 0%;
    left: 0%;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    opacity: 0;
    transition-property: opacity;
    transition-duration: 0.3s;
    transition-timing-function: ease;
}

.profile-image-container:hover .edit-overlay {
    opacity: 1;
}

.progress-container {
    max-width: 800px;
    margin-top: 2rem;
    margin-right: auto;
    margin-bottom: 2rem;
    margin-left: auto;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.step {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--secondary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    border-width: 2px;
    border-style: solid;
    border-color: var(--primary);
    z-index: 2;
}

.step.active {
    background-color: var(--primary);
    color: white;
}

.progress-bar {
    position: absolute;
    height: 4px;
    background-color: var(--primary-light);
    width: 100%;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
}

.art-form {
    background-image: linear-gradient(150deg, var(--primary-light) 20%, var(--secondary-light) 80%);
    border-radius: 20px;
    padding-top: 3rem;
    padding-right: 3rem;
    padding-bottom: 3rem;
    padding-left: 3rem;
    max-width: 800px;
    margin-top: 2rem;
    margin-right: auto;
    margin-bottom: 2rem;
    margin-left: auto;
    box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.15);
    border-width: 1px;
    border-style: solid;
    border-color: rgba(255, 255, 255, 0.3);
}

.form-step {
    display: none;
    animation-name: fadeIn;
    animation-duration: 0.3s;
    animation-timing-function: ease;
}

.form-step.active {
    display: block;
}

.form-title {
    color: var(--dark);
    border-bottom-width: 2px;
    border-bottom-style: solid;
    border-bottom-color: var(--primary);
    padding-top: 0rem;
    padding-right: 0rem;
    padding-bottom: 1rem;
    padding-left: 0rem;
    margin-top: 0rem;
    margin-right: 0rem;
    margin-bottom: 2rem;
    margin-left: 0rem;
    font-size: 1.5rem;
}

.required {
    color: #dc3545;
}

.form-control {
    background-color: rgba(255, 255, 255, 0.9);
    border-width: 2px;
    border-style: solid;
    border-color: var(--primary-dark);
    transition-property: all;
    transition-duration: 0.3s;
    transition-timing-function: ease;
    font-size: 1.1rem;
    padding-top: 1rem;
    padding-right: 1rem;
    padding-bottom: 1rem;
    padding-left: 1rem;
    margin-top: 0rem;
    margin-right: 0rem;
    margin-bottom: 1.5rem;
    margin-left: 0rem;
}

.form-control:focus {
    background-color: rgba(255, 255, 255, 1);
    border-color: var(--secondary-dark);
    box-shadow: 0px 0px 8px rgba(77, 184, 178, 0.3);
}

.btn {
    font-family: 'Nunito', sans-serif;
    font-weight: 600;
    transition-property: all;
    transition-duration: 0.4s;
    transition-timing-function: ease;
    border-width: 2px;
    border-style: solid;
    border-color: transparent;
    position: relative;
    overflow: hidden;
    z-index: 1;
    padding-top: 12px;
    padding-right: 35px;
    padding-bottom: 12px;
    padding-left: 35px;
    font-size: 1.1rem;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0%;
    left: -100%;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.5);
    transition-property: left;
    transition-duration: 0.5s;
    transition-timing-function: ease;
    z-index: -1;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background-color: var(--primary) !important;
    border-color: var(--primary) !important;
    color: #FFFFFF !important;
    box-shadow: 0px 4px 20px rgba(108, 117, 125, 0.3);
}

.btn-primary:hover {
    background-color: var(--primary-dark) !important;
    color: var(--dark) !important;
    border-color: var(--primary-dark) !important;
    transform: scale(1.05);
}

.btn-secondary {
    background-color: var(--secondary) !important;
    border-color: var(--secondary) !important;
    color: #FFFFFF !important;
    box-shadow: 0px 4px 15px rgba(108, 117, 125, 0.3);
}

.btn-secondary:hover {
    background-color: var(--secondary-dark) !important;
    color: var(--dark) !important;
    border-color: var(--secondary-dark) !important;
    transform: scale(1.05);
}

.icon-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
    margin-right: 0rem;
    margin-bottom: 1rem;
    margin-left: 0rem;
}

.icon-option {
    cursor: pointer;
    padding-top: 1rem;
    padding-right: 1rem;
    padding-bottom: 1rem;
    padding-left: 1rem;
    border-radius: 15px;
    background-color: rgba(255, 255, 255, 0.9);
    border-width: 2px;
    border-style: solid;
    border-color: var(--primary-light);
    transition-property: all;
    transition-duration: 0.3s;
    transition-timing-function: ease;
    text-align: center;
}

.icon-option.selected {
    background-color: var(--primary);
    border-color: var(--primary-dark);
    transform: scale(1.05);
}

.style-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.style-tag {
    background-color: rgba(255, 255, 255, 0.9);
    border-width: 2px;
    border-style: solid;
    border-color: var(--secondary);
    padding-top: 0.5rem;
    padding-right: 1rem;
    padding-bottom: 0.5rem;
    padding-left: 1rem;
    border-radius: 20px;
    cursor: pointer;
    transition-property: all;
    transition-duration: 0.3s;
    transition-timing-function: ease;
}

.style-tag.selected {
    background-color: var(--secondary-dark);
    color: white;
    border-color: var(--secondary-dark);
}

.budget-slider {
    width: 100%;
    height: 15px;
    border-radius: 10px;
    background-color: var(--secondary-light);
}

.invalid-feedback {
    color: #dc3545;
    display: none;
    margin-top: 0.25rem;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.artists-select {
    width: 100%;
    padding-top: 0.5rem;
    padding-right: 0.5rem;
    padding-bottom: 0.5rem;
    padding-left: 0.5rem;
    border-width: 2px;
    border-style: solid;
    border-color: var(--primary);
    border-radius: 10px;
}

.artworks-section {
    background-image: linear-gradient(150deg, var(--primary-light) 20%, var(--secondary-light) 80%);
    border-radius: 20px;
    padding-top: 3rem;
    padding-right: 3rem;
    padding-bottom: 3rem;
    padding-left: 3rem;
    max-width: 800px;
    margin-top: 2rem;
    margin-right: auto;
    margin-bottom: 2rem;
    margin-left: auto;
    box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.15);
    border-width: 1px;
    border-style: solid;
    border-color: rgba(255, 255, 255, 0.3);
}

.artworks-container {
    height: 400px;
    overflow-y: auto;
    padding-top: 1rem;
    padding-right: 1rem;
    padding-bottom: 1rem;
    padding-left: 1rem;
    border-width: 2px;
    border-style: dashed;
    border-color: var(--primary-dark);
    border-radius: 10px;
    margin-top: 1rem;
    background-color: rgba(255, 255, 255, 0.9);
}

.artwork-card {
    background-color: white;
    border-radius: 10px;
    padding-top: 1rem;
    padding-right: 1rem;
    padding-bottom: 1rem;
    padding-left: 1rem;
    margin-top: 0rem;
    margin-right: 0rem;
    margin-bottom: 1.5rem;
    margin-left: 0rem;
    box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
    opacity: 0;
    transform: translateY(20px);
    transition-property: all;
    transition-duration: 0.5s;
    transition-timing-function: ease;
}

.artwork-card.visible {
    opacity: 1;
    transform: translateY(0px);
}

.artwork-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
}

.artwork-actions {
    margin-top: 1rem;
    display: flex;
    gap: 1rem;
}

.loading-indicator {
    text-align: center;
    padding-top: 1rem;
    padding-right: 1rem;
    padding-bottom: 1rem;
    padding-left: 1rem;
    color: var(--primary);
    display: none;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0px);
    }
}

/* Footer Styles */
.mb-3 i {
    color: var(--primary) !important;
}

.mb-3 p {
    color: var(--secondary-dark);
}

.col-6 h5 {
    color: var(--primary-dark) !important;
}

.artistic-footer {
    background-color: #1a1a1a !important;
    position: relative;
}

.social-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    max-width: 200px;
}

.col-lg-4 .mb-3 i {
    color: var(--primary) !important;
}

.social-icon {
    width: 45px;
    height: 45px;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #78CAC5;
    transition-property: all;
    transition-duration: 0.3s;
    transition-timing-function: ease;
}

.social-icon:hover {
    background-color: #78CAC5;
    color: white;
    transform: rotate(15deg);
}

.art-gallery img {
    transition-property: transform;
    transition-duration: 0.3s;
    transition-timing-function: ease;
    cursor: pointer;
}

.art-gallery img:hover {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .social-grid {
        max-width: 100%;
        grid-template-columns: repeat(4, 1fr);
    }
    
    .art-gallery {
        margin-top: 2rem;
    }
}

.footer-brand .mb-3 {
    color: var(--primary);
}

/* Back to top button */
.back-top-btn {
    position: fixed;
    bottom: -50px;
    right: 30px;
    z-index: 999;
    border: none;
    outline: none;
    background-color: var(--secondary);
    color: white;
    cursor: pointer;
    padding-top: 15px;
    padding-right: 15px;
    padding-bottom: 15px;
    padding-left: 15px;
    border-radius: 50%;
    font-size: 18px;
    width: 50px;
    height: 50px;
    opacity: 0;
    transition-property: all;
    transition-duration: 0.3s;
    transition-timing-function: ease-in-out;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
}

.back-top-btn.visible {
    bottom: 30px;
    opacity: 1;
}

.back-top-btn:hover {
    background-color: var(--secondary-dark);
    transform: translateY(-2px);
}

.back-top-btn:active {
    transform: translateY(1px);
}

@media (max-width: 768px) {
    .back-top-btn {
        right: 20px;
        bottom: 20px;
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
}

/* Background edit overlay */
.edit-overlay-bg {
    position: absolute;
    top: 0%;
    left: 0%;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    opacity: 0;
    transition-property: opacity;
    transition-duration: 0.3s;
    transition-timing-function: ease;
}

.profile-header:hover .edit-overlay-bg {
    opacity: 1;
}

.fa-camera {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

/* Following Button Styles */
.following-btn {
    border-radius: 20px !important;
    padding: 6px 16px !important;
    border: 2px solid var(--primary) !important;
    color: var(--dark) !important;
    background-color: transparent !important;
    transition: all 0.3s ease !important;
    margin-top: 10px !important;
}

.following-btn:hover {
    background-color: var(--primary-light) !important;
    transform: scale(1.05) !important;
}

.following-btn span {
    font-weight: 700 !important;
    margin-right: 5px;
}

/* Following Modal Styles */
#followedArtistsModal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

#followedArtistsModal.show {
    opacity: 1;
    pointer-events: auto;
}

.modal-content {
    background-color: white;
    border-radius: 12px;
    width: 400px;
    max-width: 90%;
    max-height: 80vh;
    overflow: auto;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    padding: 16px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: var(--dark);
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    color: var(--dark);
    transition: transform 0.2s ease;
}

.close-btn:hover {
    transform: rotate(90deg);
}

.artist-item {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #f5f5f5;
    transition: background-color 0.2s ease;
}

.artist-item:hover {
    background-color: #f9f9f9;
}

.artist-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 12px;
    border: 2px solid var(--primary-light);
}

.artist-name {
    font-weight: 500;
    color: var(--dark);
}

.no-artists {
    padding: 20px;
    text-align: center;
    color: #666;
}

/* Discover Modal Styles */
#discoverModal .modal-dialog {
  max-width: none;
  width: auto;
  margin: 1rem;
}

#discoverModal .modal-content {
  background-color: var(--light);
  border-radius: 20px;
  border: 2px solid var(--primary);
}

#discoverModal .modal-header {
  border-bottom: 2px solid var(--primary);
  padding: 1.5rem;
}

#discoverModal .modal-title {
  color: var(--dark);
  font-weight: 700;
}

#artworksGallery {
  min-width: 300px; /* Minimum width before wrapping */
}

.gallery-artwork {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  transition: transform 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.gallery-artwork:hover {
  transform: translateY(-5px);
}

.gallery-artwork-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.gallery-artwork-body {
  padding: 1rem;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.gallery-artwork-title {
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 0.5rem;
}

.gallery-artwork-artist {
  color: var(--secondary-dark);
  font-size: 0.9rem;
  margin-bottom: 1rem;
}

.like-btn {
  background: none;
  border: none;
  color: #ccc;
  font-size: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 0.5rem;
  margin-top: auto;
  align-self: flex-start;
}

.like-btn.liked {
  color: #ff4757;
}

@media (max-width: 576px) {
  #discoverModal .modal-dialog {
    margin: 0.5rem;
  }
  
  #artworksGallery {
    grid-template-columns: 1fr;
  }
}
    </style>
</head>
<body>
    
    <div class="profile-header" onclick="document.getElementById('bgUpload').click()">
        <input type="file" id="bgUpload" hidden accept="image/*">
        <div class="edit-overlay-bg">
            <i class="fas fa-camera"></i>
            <div>Click to change background</div>
        </div>
    </div>

    <div class="container" >
        <div class="profile-image-container" id="profileContainer">
            <img src="placeholder.jpg" class="profile-image" id="profileImg">
            <div class="edit-overlay">Edit</div>
            <input type="file" id="avatarUpload" hidden accept="image/*">
        </div>

        
        <h1 class="editable-text d-inline-block mt-3 text-center" id="username" ><?php echo $username ?></h1>
<p class="editable-text d-inline-block lead text-muted mt-2 text-center"  >
    <?php echo  $role?>
</p>
        </>


        <div class="progress-container">
        <div class="progress-steps">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
            <div class="progress-bar"></div>
        </div>
    </div>

    <div class="art-form">
        <form id="profileForm" novalidate>
            <div class="form-step active" id="step1">
                <h3 class="form-title">Basic Information</h3>
                <div class="mb-4">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" class="form-control" pattern="[A-Za-z ]{3,}" required>
                    <div class="invalid-feedback">Please enter a valid name (letters and spaces only)</div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" class="form-control" required>
                    <div class="invalid-feedback">Please enter a valid email address</div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Shipping Address <span class="required">*</span></label>
                    <textarea class="form-control" rows="3" minlength="10" required></textarea>
                    <div class="invalid-feedback">Address must be at least 10 characters</div>
                </div>
                <div class="mb-4">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" pattern="[0-9]{10}">
                    <div class="invalid-feedback">Please enter a 10-digit phone number</div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-primary next-step">Next</button>
                </div>
            </div>

            <div class="form-step" id="step2">
                <h3 class="form-title">Art Preferences</h3>
                <div class="mb-4">
                    <label class="form-label">Favorite Medium(s) <span class="required">*</span></label>
                    <div class="icon-options">
                        <div class="icon-option" data-value="painting">
                            <i class="fas fa-palette"></i>
                            <div>Painting</div>
                            <input type="checkbox" name="mediums" value="painting" hidden>
                        </div>
                        <div class="icon-option" data-value="sculpture">
                            <i class="fas fa-monument"></i>
                            <div>Sculpture</div>
                            <input type="checkbox" name="mediums" value="sculpture" hidden>
                        </div>
                        <div class="icon-option" data-value="photography">
                            <i class="fas fa-camera"></i>
                            <div>Photography</div>
                            <input type="checkbox" name="mediums" value="photography" hidden>
                        </div>
                        <div class="icon-option" data-value="digital">
                            <i class="fas fa-laptop-code"></i>
                            <div>Digital</div>
                            <input type="checkbox" name="mediums" value="digital" hidden>
                        </div>
                    </div>
                    <div class="invalid-feedback">Please select at least one medium</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Preferred Art Styles <span class="required">*</span></label>
                    <div class="style-tags">
                        <div class="style-tag" data-value="abstract">Abstract</div>
                        <div class="style-tag" data-value="realism">Realism</div>
                        <div class="style-tag" data-value="surrealism">Surrealism</div>
                        <div class="style-tag" data-value="impressionism">Impressionism</div>
                        <div class="style-tag" data-value="contemporary">Contemporary</div>
                        <input type="hidden" name="styles" id="selectedStyles" required>
                    </div>
                    <div class="invalid-feedback">Please select at least one style</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Budget Range ($) <span class="required">*</span></label>
                    <input type="range" class="budget-slider" min="500" max="10000" step="500" value="2500" required>
                    <div class="d-flex justify-content-between mt-2">
                        <span>$500</span>
                        <span id="budgetValue">$2500</span>
                        <span>$10,000</span>
                    </div>
                    <div class="invalid-feedback">Please select a budget range</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Favorite Artists (Select up to 3)</label>
                    <select class="artists-select" multiple>
                        <option value="picasso">Pablo Picasso</option>
                        <option value="vangogh">Vincent van Gogh</option>
                        <option value="kahlo">Frida Kahlo</option>
                        <option value="warhol">Andy Warhol</option>
                        <option value="okeeffe">Georgia O'Keeffe</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary prev-step">Back</button>
                    <button type="button" class="btn btn-primary next-step">Next</button>
                </div>
            </div>

            <div class="form-step" id="step3">
                <h3 class="form-title">Review Information</h3>
                <div class="card mb-4">
                    <div class="card-body" id="reviewContent"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary prev-step">Back</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
    </div>

    <div class="artworks-section">
        <h3 class="form-title">Favorite Artworks Collection</h3>
        <div class="artworks-container" id="artworksContainer">
            <div class="text-center py-5" style="color: var(--secondary-dark);">
                <i class="fas fa-palette" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                <h4 style="color: var(--dark);">Ready to explore a world of creativity?</h4>
                <p>Discover breathtaking masterpieces waiting to inspire your collection</p>
                <p class="mt-4" style="font-weight: 600;">Ready to explore a world of creativity?</p>
                <button class="btn btn-primary mt-3" style="border-radius: 20px;" id="discoverArtworksBtn">
                    Discover Artworks
                </button>
            </div>
        </div>
        <div class="loading-indicator" style="display: none;">Loading more artworks...</div>
    </div>

    <!-- Discover Artworks Modal -->
    <div class="modal fade" id="discoverModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Discover Artworks</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div class="container-fluid">
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 p-3" id="artworksGallery">
            <!-- Artworks will be loaded here -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Footer -->
<footer class="artistic-footer container-fluid p-0 m-0">
    <div >
        <div class="row g-5">
            <!-- Brand & Social -->
            <div class="col-lg-4">
                <div class="footer-brand mb-4">
                    <h3 class=" mb-3">Artistic</h3>
                    <p class="small">Where creativity meets community</p>
                </div>
                <div class="social-grid">
                    <a href="#" class="social-icon">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-icon">
                        <i class="fab fa-behance"></i>
                    </a>
                    <a href="#" class="social-icon">
                        <i class="fab fa-dribbble"></i>
                    </a>
                    <a href="#" class="social-icon">
                        <i class="fab fa-artstation"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-6">
                <h5 class="text-primary mb-4">Create</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-light">Challenges</a></li>
                    <li class="mb-2"><a href="#" class="text-light">Tutorials</a></li>
                    <li class="mb-2"><a href="#" class="text-light">Resources</a></li>
                    <li class="mb-2"><a href="#" class="text-light">Workshops</a></li>
                </ul>
            </div>

            <!-- Community -->
            <div class="col-lg-2 col-6">
                <h5 class="text-primary mb-4">Community</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-light">Gallery</a></li>
                    <li class="mb-2"><a href="#" class="text-light">Forum</a></li>
                    <li class="mb-2"><a href="#" class="text-light">Events</a></li>
                    <li class="mb-2"><a href="#" class="text-light">Blog</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-4 col-6">
                <h5 class="text-primary mb-4">Contact</h5>
                <div class="mb-3">
                    <p class="small mb-1"><i class="fas fa-map-marker-alt me-2"></i>123 Art Street, Creative City</p>
                    <p class="small mb-1"><i class="fas fa-envelope me-2"></i>contact@arthub.com</p>
                    <p class="small"><i class="fas fa-phone me-2"></i>+1 (555) ART-HUB</p>
                </div>
                <div class="art-gallery">
                    <div class="row g-2">
                        <div class="col-4"><img src="img\pexels-pixabay-159862.jpg" class="img-fluid rounded" alt="Artwork"></div>
                        <div class="col-4"><img src="img\pexels-tiana-18128-2956376.jpg" class="img-fluid rounded" alt="Artwork"></div>
                        <div class="col-4"><img src="img\pexels-andrew-2123337.jpg" class="img-fluid rounded" alt="Artwork"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-top pt-4 mt-5 text-center">
            <p class="small mb-0 text-muted">
                &copy 24.3.2025- <?php echo date("d.m.Y")?> ArtHub. All rights reserved. 
                <a href="#" class="text-muted">Privacy</a> | 
                <a href="#" class="text-muted">Terms</a> | 
                <a href="#" class="text-muted">FAQs</a>
            </p>
        </div>
    </div>
</footer>


    <button 
        id="backToTopBtn" 
        class="back-top-btn" 
        title="Go to top"
        aria-label="Scroll to top of page"
    >
        ▲
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Initialize variables
    let currentStep = 1;
    const totalSteps = 3;
    let selectedStyleValues = [];
    let artworkPage = 1;
    let isLoadingArtworks = false;

    // Following artists data - starts empty for new users
    let followedArtists = [];
    let userArtworks = []; // Empty array for new users

    // Sample artwork data - in a real app, you'd fetch this from an API
    const sampleArtworks = [
        {
            id: 1,
            title: "Starry Night",
            artist: "Vincent van Gogh",
            imageUrl: "https://www.vangoghgallery.com/img/starry_night_full.jpg",
            liked: false
        },
        {
            id: 2,
            title: "The Persistence of Memory",
            artist: "Salvador Dalí",
            imageUrl: "https://www.moma.org/media/W1siZiIsIjMwMDAwOSJdLFsicCIsImNvbnZlcnQiLCItcXVhbGl0eSA5MCAtcmVzaXplIDIwMDB4MTQ0MFx1MDAzZSJdXQ.jpg",
            liked: false
        },
        {
            id: 3,
            title: "Girl with a Pearl Earring",
            artist: "Johannes Vermeer",
            imageUrl: "https://upload.wikimedia.org/wikipedia/commons/thumb/0/0f/1665_Girl_with_a_Pearl_Earring.jpg/800px-1665_Girl_with_a_Pearl_Earring.jpg",
            liked: false
        },
        {
            id: 4,
            title: "The Scream",
            artist: "Edvard Munch",
            imageUrl: "https://upload.wikimedia.org/wikipedia/commons/thumb/c/c5/Edvard_Munch%2C_1893%2C_The_Scream%2C_oil%2C_tempera_and_pastel_on_cardboard%2C_91_x_73_cm%2C_National_Gallery_of_Norway.jpg/800px-Edvard_Munch%2C_1893%2C_The_Scream%2C_oil%2C_tempera_and_pastel_on_cardboard%2C_91_x_73_cm%2C_National_Gallery_of_Norway.jpg",
            liked: false
        }
    ];

    // Initialize Bootstrap modal
    const discoverModal = new bootstrap.Modal(document.getElementById('discoverModal'));

    // Step Navigation
    document.querySelectorAll('.next-step').forEach(function(button) {
        button.addEventListener('click', function() {
            nextStep();
        });
    });

    document.querySelectorAll('.prev-step').forEach(function(button) {
        button.addEventListener('click', function() {
            prevStep();
        });
    });

    function nextStep() {
        if (validateStep(currentStep)) {
            document.getElementById('step' + currentStep).classList.remove('active');
            currentStep = currentStep + 1;
            updateProgress();
            document.getElementById('step' + currentStep).classList.add('active');
            
            if (currentStep === 3) {
                populateReview();
            }
        }
    }

    function prevStep() {
        document.getElementById('step' + currentStep).classList.remove('active');
        currentStep = currentStep - 1;
        updateProgress();
        document.getElementById('step' + currentStep).classList.add('active');
    }

    function validateStep(step) {
        let isValid = true;
        const currentStepEl = document.getElementById('step' + step);
        
        // Clear previous validations
        currentStepEl.querySelectorAll('.is-invalid').forEach(function(input) {
            input.classList.remove('is-invalid');
        });
        
        currentStepEl.querySelectorAll('.invalid-feedback').forEach(function(feedback) {
            feedback.style.display = 'none';
        });

        // Validate inputs
        currentStepEl.querySelectorAll('input, select, textarea').forEach(function(input) {
            if (input.checkValidity() === false) {
                isValid = false;
                input.classList.add('is-invalid');
                input.nextElementSibling.style.display = 'block';
            }
        });

        // Special validation for step 2
        if (step === 2) {
            const mediumsSelected = document.querySelectorAll('.icon-option.selected').length > 0;
            const mediumFeedback = document.querySelector('#step2 .invalid-feedback');
            if (mediumsSelected === false) {
                isValid = false;
                mediumFeedback.style.display = 'block';
            }

            const stylesSelected = document.querySelectorAll('.style-tag.selected').length > 0;
            const styleFeedback = document.querySelector('#step2 .style-tags + .invalid-feedback');
            if (stylesSelected === false) {
                isValid = false;
                styleFeedback.style.display = 'block';
            }
        }

        return isValid;
    }

    function updateProgress() {
        document.querySelectorAll('.step').forEach(function(stepElement, index) {
            if (index < currentStep) {
                stepElement.classList.add('active');
            } else {
                stepElement.classList.remove('active');
            }
        });
    }

    // Image Upload Handling
    document.getElementById('profileContainer').addEventListener('click', function() {
        document.getElementById('avatarUpload').click();
    });

    document.getElementById('avatarUpload').addEventListener('change', function(event) {
        const reader = new FileReader();
        reader.onload = function() {
            document.getElementById('profileImg').src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    });

    // Background Image Upload
    document.getElementById('bgUpload').addEventListener('change', function(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const header = document.querySelector('.profile-header');
            header.style.backgroundImage = 
                'linear-gradient(45deg, rgba(77, 184, 178, 0.6), rgba(164, 224, 221, 0.6)), ' + 
                'url(' + reader.result + ')';
        };
        reader.readAsDataURL(event.target.files[0]);
    });

    // Art Preferences Interactions
    document.querySelectorAll('.icon-option').forEach(function(option) {
        option.addEventListener('click', function() {
            this.classList.toggle('selected');
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
        });
    });

    const styleTags = document.querySelectorAll('.style-tag');
    const selectedStyles = document.getElementById('selectedStyles');
    styleTags.forEach(function(tag) {
        tag.addEventListener('click', function() {
            this.classList.toggle('selected');
            const value = this.dataset.value;
            if (selectedStyleValues.includes(value)) {
                selectedStyleValues = selectedStyleValues.filter(function(v) {
                    return v !== value;
                });
            } else {
                selectedStyleValues.push(value);
            }
            selectedStyles.value = selectedStyleValues.join(',');
        });
    });

    // Budget Slider
    const budgetSlider = document.querySelector('.budget-slider');
    const budgetValue = document.getElementById('budgetValue');
    budgetSlider.addEventListener('input', function() {
        budgetValue.textContent = '$' + this.value;
    });

    // Artist Selection
    const artistSelect = document.querySelector('.artists-select');
    artistSelect.addEventListener('change', function() {
        if (this.selectedOptions.length > 3) {
            alert('Maximum 3 artists allowed');
            this.selectedOptions[this.selectedOptions.length-1].selected = false;
        }
    });

    // Function to load artworks into the gallery
// Update your loadArtworksGallery function to use the new structure
function loadArtworksGallery() {
    const gallery = document.getElementById('artworksGallery');
    gallery.innerHTML = '';
    
    sampleArtworks.forEach(artwork => {
        const artworkElement = document.createElement('div');
        artworkElement.className = 'col';
        artworkElement.innerHTML = `
            <div class="gallery-artwork">
                <img src="${artwork.imageUrl}" class="gallery-artwork-img" alt="${artwork.title}">
                <div class="gallery-artwork-body">
                    <h5 class="gallery-artwork-title">${artwork.title}</h5>
                    <p class="gallery-artwork-artist">${artwork.artist}</p>
                    <button class="like-btn ${artwork.liked ? 'liked' : ''}" data-id="${artwork.id}">
                        <i class="fas fa-heart"></i> Like
                    </button>
                </div>
            </div>
        `;
        gallery.appendChild(artworkElement);
    });
}

    // Function to handle liking an artwork
    function likeArtwork(artworkId) {
        const artwork = sampleArtworks.find(a => a.id === artworkId);
        if (artwork) {
            artwork.liked = !artwork.liked;
            
            // Update the like button
            const likeBtn = document.querySelector(`.like-btn[data-id="${artworkId}"]`);
            if (likeBtn) {
                likeBtn.classList.toggle('liked');
            }
            
            // If liked, add to favorites
            if (artwork.liked) {
                addUserArtwork(artwork.imageUrl);
            }
        }
    }

    // Function to add an artwork to user's favorites
    function addUserArtwork(url) {
        userArtworks.push(url);
        const artworksContainer = document.getElementById('artworksContainer');
        
        // Clear empty state if it exists
        if (artworksContainer.querySelector('.text-center')) {
            artworksContainer.innerHTML = '';
        }
        
        // Create and add the new artwork card
        artworksContainer.appendChild(createArtworkCard(url));
    }

    // Function to create an artwork card
    function createArtworkCard(url) {
        const card = document.createElement('div');
        card.className = 'artwork-card';
        card.innerHTML = [
            '<img src="' + url + '" class="artwork-image" alt="Favorite artwork">',
            '<div class="artwork-actions">',
            '  <button class="btn btn-primary btn-sm"><i class="fas fa-heart"></i> Like</button>',
            '  <button class="btn btn-secondary btn-sm"><i class="fas fa-share"></i> Share</button>',
            '</div>'
        ].join('');
        
        // Add animation
        setTimeout(function() {
            card.classList.add('visible');
        }, 100);
        
        return card;
    }

    // Initialize empty artworks collection
    function initializeEmptyArtworks() {
        const artworksContainer = document.getElementById('artworksContainer');
        artworksContainer.innerHTML = `
            <div class="text-center py-5" style="color: var(--secondary-dark);">
                <i class="fas fa-palette" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                <h4 style="color: var(--dark);">No saved artworks yet</h4>
                <p>Provide to a virtual and 25% of bugs in line</p>
                <p class="mt-4" style="font-weight: 600;">Welcome to your blog</p>
                <button class="btn btn-primary mt-3" style="border-radius: 20px;" id="discoverArtworksBtn">
                    Discover Artworks
                </button>
            </div>
        `;
        
        // Add event listener to the button
        document.getElementById('discoverArtworksBtn')?.addEventListener('click', function() {
            loadArtworksGallery();
            discoverModal.show();
        });
    }

    function populateReview() {
        const reviewContent = document.getElementById('reviewContent');
        const selectedMediums = Array.from(document.querySelectorAll('.icon-option.selected div'))
                               .map(function(div) { return div.textContent; })
                               .join(', ');

        const formData = {
            name: document.querySelector('#step1 input[type="text"]').value,
            email: document.querySelector('#step1 input[type="email"]').value,
            address: document.querySelector('#step1 textarea').value,
            phone: document.querySelector('#step1 input[type="tel"]').value || 'Not provided',
            mediums: selectedMediums,
            styles: selectedStyleValues.join(', '),
            budget: '$' + budgetSlider.value,
            artists: Array.from(artistSelect.selectedOptions).map(function(opt) { return opt.text; }).join(', ') || 'None selected'
        };

        reviewContent.innerHTML = [
            '<h5>Basic Information</h5>',
            '<p><strong>Name:</strong> ' + formData.name + '</p>',
            '<p><strong>Email:</strong> ' + formData.email + '</p>',
            '<p><strong>Address:</strong> ' + formData.address + '</p>',
            '<p><strong>Phone:</strong> ' + formData.phone + '</p>',
            '<h5 class="mt-4">Art Preferences</h5>',
            '<p><strong>Medium(s):</strong> ' + formData.mediums + '</p>',
            '<p><strong>Styles:</strong> ' + formData.styles + '</p>',
            '<p><strong>Budget:</strong> ' + formData.budget + '</p>',
            '<p><strong>Favorite Artists:</strong> ' + formData.artists + '</p>'
        ].join('');
    }

    // Form Submission
    document.getElementById('profileForm').addEventListener('submit', function(event) {
        event.preventDefault();
        if (validateStep(currentStep)) {
            alert('Profile submitted successfully!\n\n(Note: This is a demo)');
            
            // Reset form
            this.reset();
            currentStep = 1;
            selectedStyleValues = [];
            userArtworks = [];
            budgetSlider.value = 2500;
            budgetValue.textContent = '$2500';
            document.querySelectorAll('.icon-option, .style-tag').forEach(function(el) {
                el.classList.remove('selected');
            });
            document.querySelectorAll('.form-step').forEach(function(step) {
                step.classList.remove('active');
            });
            document.getElementById('step1').classList.add('active');
            updateProgress();
            document.getElementById('profileImg').src = 'placeholder.jpg';
            document.querySelector('.profile-header').style.backgroundImage = 
                'linear-gradient(45deg, rgba(77, 184, 178, 0.6), rgba(164, 224, 221, 0.6))';
            
            // Reset artworks to empty state
            initializeEmptyArtworks();
            artworkPage = 1;
        }
    });

    // Back to Top Button
    window.addEventListener('scroll', function() {
        const btn = document.getElementById('backToTopBtn');
        if (window.scrollY > 300) {
            btn.classList.add('visible');
        } else {
            btn.classList.remove('visible');
        }
    });

    document.getElementById('backToTopBtn').addEventListener('click', function(event) {
        event.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Event delegation for like buttons in the modal
    document.addEventListener('click', function(e) {
        if (e.target.closest('.like-btn')) {
            const artworkId = parseInt(e.target.closest('.like-btn').dataset.id);
            likeArtwork(artworkId);
        }
    });

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        addFollowingButton();
        initializeEmptyArtworks(); // Start with empty artworks collection
    });

    // Following artists functionality (existing code)
    function createFollowedArtistsModal() {
        // Create modal container
        const modal = document.createElement('div');
        modal.id = 'followedArtistsModal';
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0,0,0,0.7)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = '1000';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        modal.style.pointerEvents = 'none';
        
        // Create modal content
        const modalContent = document.createElement('div');
        modalContent.style.backgroundColor = 'white';
        modalContent.style.borderRadius = '12px';
        modalContent.style.width = '400px';
        modalContent.style.maxWidth = '90%';
        modalContent.style.maxHeight = '80vh';
        modalContent.style.overflow = 'auto';
        modalContent.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
        
        // Create modal header
        const modalHeader = document.createElement('div');
        modalHeader.style.padding = '16px';
        modalHeader.style.borderBottom = '1px solid #eee';
        modalHeader.style.display = 'flex';
        modalHeader.style.justifyContent = 'space-between';
        modalHeader.style.alignItems = 'center';
        
        const modalTitle = document.createElement('h5');
        modalTitle.textContent = 'Following';
        modalTitle.style.margin = '0';
        modalTitle.style.fontSize = '18px';
        modalTitle.style.fontWeight = '600';
        modalTitle.style.color = 'var(--dark)';
        
        const closeButton = document.createElement('button');
        closeButton.innerHTML = '&times;';
        closeButton.style.background = 'none';
        closeButton.style.border = 'none';
        closeButton.style.fontSize = '24px';
        closeButton.style.cursor = 'pointer';
        closeButton.style.padding = '0';
        closeButton.style.color = 'var(--dark)';
        closeButton.addEventListener('click', closeFollowedArtistsModal);
        
        modalHeader.appendChild(modalTitle);
        modalHeader.appendChild(closeButton);
        
        // Create artists list
        const artistsList = document.createElement('div');
        
        if (followedArtists.length > 0) {
            followedArtists.forEach(artist => {
                const artistItem = document.createElement('div');
                artistItem.style.padding = '12px 16px';
                artistItem.style.display = 'flex';
                artistItem.style.alignItems = 'center';
                artistItem.style.borderBottom = '1px solid #f5f5f5';
                artistItem.style.transition = 'background-color 0.2s ease';
                
                const artistAvatar = document.createElement('img');
                artistAvatar.src = artist.avatar;
                artistAvatar.style.width = '44px';
                artistAvatar.style.height = '44px';
                artistAvatar.style.borderRadius = '50%';
                artistAvatar.style.objectFit = 'cover';
                artistAvatar.style.marginRight = '12px';
                artistAvatar.style.border = '2px solid var(--primary-light)';
                
                const artistName = document.createElement('span');
                artistName.textContent = artist.name;
                artistName.style.fontWeight = '500';
                artistName.style.color = 'var(--dark)';
                
                artistItem.appendChild(artistAvatar);
                artistItem.appendChild(artistName);
                artistsList.appendChild(artistItem);
            });
        } else {
            const noArtists = document.createElement('div');
            noArtists.style.padding = '20px';
            noArtists.style.textAlign = 'center';
            noArtists.style.color = 'var(--secondary-dark)';
            noArtists.innerHTML = `
                <i class="fas fa-user-friends" style="font-size: 2rem; color: var(--primary); margin-bottom: 1rem;"></i>
                <p style="margin-bottom: 0;">You're not following any artists yet</p>
                <p style="font-size: 0.9rem; color: var(--dark);">Discover and follow your favorite artists</p>
                <button class="btn btn-primary mt-2" style="border-radius: 20px; padding: 6px 20px;">
                    Browse Artists
                </button>
            `;
            artistsList.appendChild(noArtists);
        }
        
        // Assemble modal
        modalContent.appendChild(modalHeader);
        modalContent.appendChild(artistsList);
        modal.appendChild(modalContent);
        
        // Add to document
        document.body.appendChild(modal);
        
        // Show modal with animation
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.style.pointerEvents = 'auto';
        }, 10);
        
        // Close when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeFollowedArtistsModal();
            }
        });
    }

    function closeFollowedArtistsModal() {
        const modal = document.getElementById('followedArtistsModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.style.pointerEvents = 'none';
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    }

    // Add following button to profile
    function addFollowingButton() {
        const profileContainer = document.querySelector('.container.text-center');
        
        const followingButton = document.createElement('button');
        followingButton.className = 'btn following-btn d-block mx-auto mt-3';
        followingButton.innerHTML = `
            <span style="font-weight:600">${followedArtists.length}</span> Following
        `;
        
        // Style based on whether following anyone
        if (followedArtists.length > 0) {
            followingButton.style.backgroundColor = 'var(--primary)';
            followingButton.style.borderColor = 'var(--primary-dark)';
            followingButton.style.color = 'white';
        } else {
            followingButton.style.backgroundColor = 'var(--secondary-light)';
            followingButton.style.borderColor = 'var(--secondary)';
            followingButton.style.color = 'var(--dark)';
        }
        
        followingButton.addEventListener('click', function(e) {
            e.preventDefault();
            createFollowedArtistsModal();
        });
        

    }
    </script>
</body>
</html>