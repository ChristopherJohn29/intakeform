<img src="<?php echo base_url() ?>/dist/img/pdf_header_portrait.png">

<p style="margin:0; padding:0;"><span style="font-size:16px;"><?php echo $record['providerName']; ?></span><br>
<span style="font-size:10px; color: gray; padding-top:6px">Date of Service:</span> <?php echo $record['dateOfService']; ?></p>

<p style="font-size:8px; color: gray;">ROUTE SHEET</p>

<table style="font-size: 10px;padding: 5px;">
	<thead>
		<tr>
			<th width="120px" bgcolor="#548bb8" style="color: white;border:1px solid #548bb8; font-weight:bold;">Time</th>
			<th width="70px" bgcolor="#548bb8" style="color: white;border:1px solid #548bb8; font-weight:bold;">Company</th>
			<th width="170px" bgcolor="#548bb8" style="color: white;border:1px solid #548bb8; font-weight:bold;">Patient's Info</th>
			<th width="170px" bgcolor="#548bb8" style="color: white;border:1px solid #548bb8; font-weight:bold;">Home Health Info</th>
			<th width="240px" bgcolor="#548bb8" style="color: white;border:1px solid #548bb8; font-weight:bold;">Notes</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($lists as $list): ?>
			<tr>
				<td width="120px" style="border-bottom: 1px solid #d2d6de;">
					<?php echo $list['time']; ?>		
				</td>
				<td width="70px" style="border-bottom: 1px solid #d2d6de;">
					<?php echo $list['company']; ?>
				</td>
				<td width="170px" style="border-bottom: 1px solid #d2d6de;">
					<?php echo $list['patientName']; ?><br>
					<?php echo $list['patientAddress']; ?><br>
					<?php echo $list['patientPhoneNum']; ?><br><br>
					<strong>Supervising MD:</strong> <?php echo $list['supervisingMD_firstname'] . ' ' . $list['supervisingMD_lastname']; ?>
				</td>
	            <td width="170px" style="border-bottom: 1px solid #d2d6de;">
	            	<?php echo $list['homeHealthName']; ?><br>
	            	<?php echo $list['homeHealthContactName']; ?><br>
    				<?php echo $list['homeHealthPhoneNum']; ?>
	            </td>
				<td width="240px" style="border-bottom: 1px solid #d2d6de;">
					Type of Visit : <?php echo $list['tovName']; ?><br>
					Other Notes: <br>
					<?php echo nl2br($list['notes']); ?>
				</td>
			</tr>			
		<?php endforeach; ?>
	</tbody>
</table>
