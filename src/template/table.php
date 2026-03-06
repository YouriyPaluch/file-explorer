<?php
use YouriyPaluch\FileExplorer\DocumentEntity;
/** @var DocumentEntity[] $documentEntityList */

if (!empty($documentEntityList)): ?>
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th class="name-header">Name</th>
				<th class="size-header">Size</th>
				<th class="created-header">Create</th>
				<th class="modified-header">Modify</th>
				<th class="action-header">Action</th>
			</tr>
		</thead>
		<tbody>
			<?php for ($i = 0; $i < count($documentEntityList); $i++):
				$documentEntity = $documentEntityList[$i];
			?>
				<tr class="<?php echo ($i % 2) ? 'row-odd' : 'row-even' ?>">
					<td class="name">
						<a href="<?php echo $documentEntity->getUrl(); ?>" <?php echo $documentEntity->isFile() ? 'target="_blank"' : ''; ?>>
							<?php if ($documentEntity->isDir()): ?>
								<!-- Іконка Папки (SVG) -->
								<svg class="dir-icon-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M10 25C10 22.2386 12.2386 20 15 20H35L45 30H85C87.7614 30 90 32.2386 90 35V80C90 82.7614 87.7614 85 85 85H15C12.2386 85 10 82.7614 10 80V25Z" fill="#79C4F2"/>
									<path d="M10 40H90V80C90 82.7614 87.7614 85 85 85H15C12.2386 85 10 82.7614 10 80V40Z" fill="#4A85E8"/>
								</svg>
							<?php else: ?>
								<!-- Іконка Файлу (SVG) -->
								<svg class="file-icon-svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M20 15C20 12.2386 22.2386 10 25 10H60L80 30V85C80 87.7614 77.7614 90 75 90H25C22.2386 90 20 87.7614 20 85V15Z" fill="#F2F2F2"/>
									<path d="M60 10V30H80" fill="#D9D9D9"/>
									<path d="M30 45H70" stroke="#A0A0A0" stroke-width="4" stroke-linecap="round"/>
									<path d="M30 60H70" stroke="#A0A0A0" stroke-width="4" stroke-linecap="round"/>
									<path d="M30 75H50" stroke="#A0A0A0" stroke-width="4" stroke-linecap="round"/>
								</svg>
							<?php endif; ?>
							<span><?php echo htmlspecialchars($documentEntity->getName()); ?></span>
						</a>
					</td>
					<td class="size"><?php echo $documentEntity->getSize(); ?></td>
					<td class="created"><?php echo $documentEntity->getCreatedAt(); ?></td>
					<td class="modified"><?php echo $documentEntity->getModifiedAt(); ?></td>
					<td class="action download-btn">
						<?php if ($documentEntity->isFile()): ?>
							<a class="fe-btn" href="<?php echo $documentEntity->getDownloadUrl(); ?>">
								Download
							</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endfor; ?>
		</tbody>
	</table>
<?php else: ?>
	<p style="color: #999; width: 100%; text-align: center;">Nothing to show</p>
<?php endif; ?>
