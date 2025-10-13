<?php
// Read and decode the JSON data
$contactData = json_decode(file_get_contents('data/contact.json'), true);
include 'header.php';
?>

<!-- Start Inner Page Hero -->
<div class="custom-hero2-about" style="background-image: url('<?php echo $contactData['hero']['backgroundImage']; ?>');">
	<div class="container">
		<div class="row justify-content-center text-center">
			<div class="col-lg-8">
				<div class="custom-hero2-content">
					<h1><?php echo $contactData['hero']['title']; ?></h1>
					<p class="mb-4">
						<?php echo $contactData['hero']['description']; ?>
					</p>
					<p>
						<?php foreach ($contactData['hero']['buttons'] as $button): ?>
							<a href="<?php echo $button['url']; ?>" class="<?php echo $button['class']; ?>"><?php echo $button['text']; ?></a>
						<?php endforeach; ?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Inner Page Hero -->


<!-- Start Contact Form -->
<div class="untree_co-section">
	<div class="container">

		<div class="block">
			<div class="row justify-content-center">


				<div class="col-md-8 col-lg-8 pb-4">


					<div class="row mb-5">
						<div class="col-lg-4">
							<div class="service no-shadow align-items-center link horizontal d-flex active" data-aos="fade-left" data-aos-delay="0">
								<div class="service-icon color-1 mb-4">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="<?php echo $contactData['contactInfo']['address']['icon']; ?>" viewBox="0 0 16 16">
										<path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z" />
									</svg>
								</div> <!-- /.icon -->
								<div class="service-contents">
									<p><?php echo $contactData['contactInfo']['address']['text']; ?></p>
								</div> <!-- /.service-contents-->
							</div> <!-- /.service -->
						</div>

						<div class="col-lg-4">
							<div class="service no-shadow align-items-center link horizontal d-flex active" data-aos="fade-left" data-aos-delay="0">
								<div class="service-icon color-1 mb-4">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="<?php echo $contactData['contactInfo']['email']['icon']; ?>" viewBox="0 0 16 16">
										<path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414.05 3.555zM0 4.697v7.104l5.803-3.558L0 4.697zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586l-1.239-.757zm3.436-.586L16 11.801V4.697l-5.803 3.546z" />
									</svg>
								</div> <!-- /.icon -->
								<div class="service-contents">
									<p><?php echo $contactData['contactInfo']['email']['text']; ?></p>
								</div> <!-- /.service-contents-->
							</div> <!-- /.service -->
						</div>

						<div class="col-lg-4">
							<div class="service no-shadow align-items-center link horizontal d-flex active" data-aos="fade-left" data-aos-delay="0">
								<div class="service-icon color-1 mb-4">
									<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="<?php echo $contactData['contactInfo']['phone']['icon']; ?>" viewBox="0 0 16 16">
										<path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z" />
									</svg>
								</div> <!-- /.icon -->
								<div class="service-contents">
									<p><?php echo $contactData['contactInfo']['phone']['text']; ?></p>
								</div> <!-- /.service-contents-->
							</div> <!-- /.service -->
						</div>
					</div>

					<form>
						<div class="row">
							<div class="col-6">
								<div class="form-group">
									<label class="text-black" for="<?php echo $contactData['form']['firstName']['id']; ?>"><?php echo $contactData['form']['firstName']['label']; ?></label>
									<input type="<?php echo $contactData['form']['firstName']['type']; ?>" class="form-control" id="<?php echo $contactData['form']['firstName']['id']; ?>">
								</div>
							</div>
							<div class="col-6">
								<div class="form-group">
									<label class="text-black" for="<?php echo $contactData['form']['lastName']['id']; ?>"><?php echo $contactData['form']['lastName']['label']; ?></label>
									<input type="<?php echo $contactData['form']['lastName']['type']; ?>" class="form-control" id="<?php echo $contactData['form']['lastName']['id']; ?>">
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="text-black" for="<?php echo $contactData['form']['email']['id']; ?>"><?php echo $contactData['form']['email']['label']; ?></label>
							<input type="<?php echo $contactData['form']['email']['type']; ?>" class="form-control" id="<?php echo $contactData['form']['email']['id']; ?>">
						</div>

						<div class="form-group mb-5">
							<label class="text-black" for="<?php echo $contactData['form']['message']['id']; ?>"><?php echo $contactData['form']['message']['label']; ?></label>
							<textarea name="" class="form-control" id="<?php echo $contactData['form']['message']['id']; ?>" cols="<?php echo $contactData['form']['message']['cols']; ?>" rows="<?php echo $contactData['form']['message']['rows']; ?>"></textarea>
						</div>

						<button type="submit" class="<?php echo $contactData['form']['submitButton']['class']; ?>"><?php echo $contactData['form']['submitButton']['text']; ?></button>
					</form>

				</div>

			</div>

		</div>


	</div>

	<?php if (isset($contactData['map']['iframe'])): ?>
		<div class="mt-5 mb-5">
			<div class="custom-map-wrapper">
				<?php echo $contactData['map']['iframe']; ?>
			</div>
		</div>
	<?php endif; ?>


</div>
</div>

<!-- End Contact Form -->

<?php include 'footer.php'; ?>