<?php use YouriyPaluch\FileExplorer\DocumentEntity;
/** @var DocumentEntity[] $documentEntityList */

if (!empty($documentEntityList)): ?>
	<?php foreach ($documentEntityList as $documentEntity): ?>
		<a href="<?php echo $documentEntity->getUrl(); ?>" <?php echo $documentEntity->isFile() ? 'target="_blank"' : ''; ?>>
			<span class="file-item" title="<?php echo htmlspecialchars($documentEntity->getName()); ?>">
				<span class="file-icon">
					<?php if ($documentEntity->isDir()): ?>
						<!-- Іконка Папки (SVG) -->
						<svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
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
				</span>
				<span class="file-name">
					<?php echo htmlspecialchars($documentEntity->getName()); ?>
				</span>
			</span>
		</a>
	<?php endforeach; ?>
<?php else: ?>
	<p style="color: #999; width: 100%; text-align: center;">Nothing to show</p>
<?php endif; ?>
