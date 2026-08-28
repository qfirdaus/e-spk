<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controllers/MaklumatKursusPKController.php';

$kursusId = isset($_GET['course']) ? (int)$_GET['course'] : 0;

if ($kursusId === 0) {
    die("ID Kursus tidak sah.");
}

$controller = new MaklumatKursusPKController();
$data = $controller->getExcelData($kursusId);

$course = $data['course']; 
$df = "Table4_" . ($course['kod_kursus'] ?? 'Unknown') . "_" . date('Ymd');

$plo_num_max = 12;
$plo_num_col = $plo_num_max + 2;

$clo_count = count($data['clos']);
$clo_num_row = $clo_count + 1;

$skill_count = count($data['skills']);
$cco_count = count($data['cco']);
$continuous_count = count($data['continuous']);
$final_count = count($data['final']);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$df.xls\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head>
        <meta charset="utf-8">
        <style>
            .page {
                -webkit-transform: rotate(-90deg); 
                -moz-transform:rotate(-90deg);
                filter:progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
            }
            .tb4 th, .tb4 td {
                font-size: 9px;
                font-family: Arial, sans-serif;
            }
            .tb4 th {
                background-color: #D3D3D3; /* lightgrey */
                text-align: left;
                font-weight: normal;
            }
        </style>
    </head>
    <body>
        <table class='tb4' border='1'> 
            <tbody>
                <!-- Number 1 -->
                <tr>
                    <th rowspan='2' width='15'>1</th>
                    <th width='100'>Name of Course :</th>
                    <td colspan='<?= $plo_num_col ?>' width='500'><?= htmlspecialchars($course['subjekbm'] ?? '') ?></td> 
                </tr>
                <tr>
                    <th>Course Code :</th>
                    <td colspan='<?= $plo_num_col ?>'><?= htmlspecialchars($course['kod_kursus'] ?? '') ?></td> 
                </tr>

                <!-- Number 2 -->
                <tr>
                    <th>2</th>
                    <th>Synopsis :</th>
                    <td colspan='<?= $plo_num_col ?>'><?= htmlspecialchars($course['sinopsis_bm'] ?? '') ?></td>
                </tr>

                <!-- Number 3 -->
                <tr>
                    <th>3</th>
                    <th>Name(s) of academic staff :</th>
                    <td colspan='<?= $plo_num_col ?>'><?= htmlspecialchars($course['gelar_nama'] ?? '') ?></td>
                </tr>

                <!-- Number 4 -->
                <tr>
                    <th>4</th>
                    <th>Semester and Year offered :</th>
                    <th colspan='2'>Semester</th>
                    <td style='text-align:left'><?= htmlspecialchars($course['sem_pengajian'] ?? '') ?></td>
                    <th colspan='2'>Year</th>
                    <td style='text-align:left'><?= htmlspecialchars($course['tahun_pengajian'] ?? '') ?></td>
                    <td bgcolor='black' colspan='<?= ($plo_num_col - 6) ?>'></td>
                </tr>

                <!-- Number 5 -->
                <tr>
                    <th>5</th>
                    <th>Credit Value :</th>
                    <td style='text-align:left' colspan='<?= $plo_num_col ?>'><?= htmlspecialchars($course['kredit'] ?? '') ?></td>
                </tr>

                <!-- Number 6 -->
                <tr>
                    <th>6</th>
                    <th>Prerequisite/co-requisite : (if any)</th>
                    <td colspan='<?= $plo_num_col ?>'><?= htmlspecialchars($data['prerequisite'] ?? '') ?></td>
                </tr>

                <!-- Number 7 -->
                <tr>
                    <th rowspan='<?= $clo_num_row ?>'>7</th>
                    <th colspan='<?= ($plo_num_col + 1) ?>'>Course Learning Outcomes (CLO) : At the end of the course the student will be able to : <br/> (example) - explain the basic principles of immunisation (C2,PLO1)</th>
                </tr>
                <?php foreach ($data['clos'] as $clo): ?>
                <tr>
                    <th><?= htmlspecialchars($clo['kod_clo']) ?></th>
                    <td style='text-align:left' colspan='<?= $plo_num_col ?>'><?= htmlspecialchars($clo['keterangan_bm']) ?></td> 
                </tr>
                <?php endforeach; ?>

                <!-- Number 8 -->
                <tr>
                    <th rowspan='<?= ($clo_count + 3) ?>'>8</th>
                    <th colspan='<?= ($plo_num_col + 1) ?>'>Mapping of the Course Learning Outcomes to the Programme Learning Outcomes, Teaching Methods and Assessment :</th>
                </tr>
                <tr> 
                    <th style='text-align:center' rowspan='2'>Course Learning Outcomes (CLO)</th>
                    <th style='text-align:center' colspan='<?= $plo_num_max ?>'>Programme Learning Outcomes (PLO)</th>
                    <th style='text-align:center' rowspan='2' width='30'>Teaching Methods</th>
                    <th style='text-align:center' rowspan='2' width='30'>Assessment</th>
                </tr>
                <tr>
                    <?php for ($col = 1; $col <= $plo_num_max; $col++): ?>
                        <th style='text-align:center'>PLO<?= $col ?></th>
                    <?php endfor; ?>
                </tr>

                <!-- CLO - PLO Mapping -->
                <?php foreach ($data['clos'] as $clo): ?>
                <tr>
                    <th><?= htmlspecialchars($clo['kod_clo']) ?></th>
                    <?php for ($col = 1; $col <= $plo_num_max; $col++): ?>
                        <?php $ploCode = "PLO" . $col; ?>
                        <?php if (in_array($ploCode, $clo['plos'] ?? [])): ?>
                            <td style='text-align:center'>/</td>
                        <?php else: ?>
                            <td></td>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <td><?= htmlspecialchars($clo['teaching_methods'] ?? '') ?></td>
                    <td><?= htmlspecialchars($clo['assessments'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>

                <!-- Number 9 -->
                <?php if ($skill_count == 0): ?>
                    <tr>
                        <th>9</th>
                        <th colspan='6'>
                                Transferable Skills (if applicable)
                                <br>
                                (Skills learned in the course of study which can be useful and utilized in other settings)
                        </th>
                        <th></th>
                        <td colspan='8'></td>
                    </tr>
                <?php else: ?>
                    <?php $col = 1; foreach ($data['skills'] as $skill): ?>
                        <tr>
                            <?php if ($col == 1): ?>
                            <th rowspan='<?= $skill_count ?>'>9</th>
                            <th colspan='6' rowspan='<?= $skill_count ?>'>
                                Transferable Skills (if applicable)
                                <br>
                                (Skills learned in the course of study which can be useful and utilized in other settings)
                            </th>
                            <?php endif; ?>
                            <th style='text-align:center'><?= $col ?></th>
                            <td style='text-align:left' colspan='8'><?= htmlspecialchars($skill['kemahiran'] ?? '') ?></td> 
                        </tr>
                        <?php $col++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Number 10 -->
                <tr>
                    <th rowspan='<?= ($cco_count + $continuous_count + $final_count + 12) ?>'>10</th>
                    <th colspan='<?= ($plo_num_col + 1) ?>'>Distribution of Student Learning Time (SLT)</th>
                </tr>
                <tr>
                    <th rowspan='3' colspan='7' style='vertical-align: middle; text-align: center'>Course Content Outline</th>
                    <th rowspan='3' style='vertical-align: middle; text-align: center'>CLO</th>
                    <th colspan='6' style='text-align: center'>Teaching and Learning Activities</th>
                    <th rowspan='3' style='vertical-align: middle; text-align: center'>SLT</th>
                </tr>
                <tr>
                    <th colspan='4' style='text-align: center'>Guided Learning (F2F)</th>
                    <th rowspan='2' style='vertical-align: middle; text-align: center'>Guided Learning (NF2F)</th>
                    <th rowspan='2' style='vertical-align: middle; text-align: center'>Independent Learning (NF2F)</th>
                </tr>
                <tr>
                    <th style='text-align: center'>L</th>
                    <th style='text-align: center'>T</th>
                    <th style='text-align: center'>P</th>
                    <th style='text-align: center'>O</th>
                </tr>

                <?php $totalslt_cco = 0; ?>
                <?php foreach ($data['cco'] as $cco): ?>
                    <tr style='text-align: center'>
                        <td colspan='7' style='text-align: left'><?= htmlspecialchars($cco["content_outline"] ?? '') ?></td>
                        <td><?= htmlspecialchars($cco["kod_clo"] ?? '') ?></td>
                        <td><?= htmlspecialchars($cco["f2f_lecture"] ?? '') ?></td>
                        <td><?= htmlspecialchars($cco["f2f_tutorial"] ?? '') ?></td>
                        <td><?= htmlspecialchars($cco["f2f_practical"] ?? '') ?></td>
                        <td><?= htmlspecialchars($cco["f2f_others"] ?? '') ?></td>
                        <td><?= htmlspecialchars($cco["nf2f_guided"] ?? '') ?></td>
                        <td><?= htmlspecialchars($cco["nf2f_independent"] ?? '') ?></td>
                        <th style='text-align: center'><?= htmlspecialchars($cco["slt"] ?? 0) ?></th>
                    </tr>
                    <?php $totalslt_cco += (float)($cco["slt"] ?? 0); ?>
                <?php endforeach; ?>

                <tr>
                    <td colspan='<?= $plo_num_col ?>' style='text-align: right'> Total &nbsp;</td>
                    <th style='text-align: center'><?= $totalslt_cco ?></th>
                </tr>
                <tr>
                     <td colspan='<?= ($plo_num_col + 1) ?>'></td>
                </tr>

                <!-- Continuous Assessment -->
                <tr>
                    <th colspan='7' style='vertical-align: middle; text-align: center'>Continuous Assessment</th>
                    <th colspan='3' style='vertical-align: middle; text-align: center'>Percentage (%)</th>
                    <th colspan='2' style='text-align: center'>F2F</th>
                    <th colspan='2' style='text-align: center'>NF2F</th>
                    <th style='vertical-align: middle; text-align: center'>SLT</th>
                </tr>

                <?php 
                $totalslt_continuous = 0; 
                $col = 1; 
                ?>
                <?php foreach ($data['continuous'] as $cont): ?>
                    <tr>
                        <th style='text-align: center'><?= $col ?></th>
                        <td colspan='6'><?= htmlspecialchars($cont["penilaian"] ?? '') ?></td>
                        <td colspan='3' style='text-align: center'><?= htmlspecialchars($cont["percentage"] ?? 0) ?></td>
                        <td colspan='2' style='text-align: center'><?= htmlspecialchars($cont["f2f"] ?? 0) ?></td>
                        <td colspan='2' style='text-align: center'><?= htmlspecialchars($cont["nf2f"] ?? 0) ?></td>
                        <th style='text-align: center'><?= htmlspecialchars($cont["slt"] ?? 0) ?></th>
                    </tr>
                    <?php 
                    $totalslt_continuous += (float)($cont["slt"] ?? 0);
                    $col++; 
                    ?>
                <?php endforeach; ?>

                <tr>
                    <td colspan='<?= $plo_num_col ?>' style='text-align: right'> Total &nbsp;</td>
                    <th style='text-align: center'><?= $totalslt_continuous ?></th>
                </tr>
                <tr>
                    <td colspan='<?= ($plo_num_col + 1) ?>'></td>
                </tr>

                <!-- Final Assessment -->
                <tr>
                    <th colspan='7' style='vertical-align: middle; text-align: center'>Final Assessment</th>
                    <th colspan='3' style='vertical-align: middle; text-align: center'>Percentage (%)</th>
                    <th colspan='2' style='text-align: center'>F2F</th>
                    <th colspan='2' style='text-align: center'>NF2F</th>
                    <th style='vertical-align: middle; text-align: center'>SLT</th>
                </tr>

                <?php 
                $totalslt_final = 0; 
                $col = 1; 
                ?>
                <?php foreach ($data['final'] as $fin): ?>
                    <tr>
                        <th style='text-align: center'><?= $col ?></th>
                        <td colspan='6'><?= htmlspecialchars($fin["penilaian"] ?? '') ?></td>
                        <td colspan='3' style='text-align: center'><?= htmlspecialchars($fin["percentage"] ?? 0) ?></td>
                        <td colspan='2' style='text-align: center'><?= htmlspecialchars($fin["f2f"] ?? 0) ?></td>
                        <td colspan='2' style='text-align: center'><?= htmlspecialchars($fin["nf2f"] ?? 0) ?></td>
                        <th style='text-align: center'><?= htmlspecialchars($fin["slt"] ?? 0) ?></th>
                    </tr>
                    <?php 
                    $totalslt_final += (float)($fin["slt"] ?? 0);
                    $col++; 
                    ?>
                <?php endforeach; ?>

                <tr>
                    <td colspan='<?= $plo_num_col ?>' style='text-align: right'> Total &nbsp; </td>
                    <th style='text-align: center'><?= $totalslt_final ?></th>
                </tr>
                <tr>
                    <td colspan='<?= $plo_num_col ?>' style='text-align: right'> Grand Total SLT &nbsp; </td>
                    <th style='text-align: center'><?= ($totalslt_cco + $totalslt_continuous + $totalslt_final) ?></th>
                </tr>

                <!-- Number 11 -->
                <tr>
                    <th>11</th>
                    <th colspan='2'>Identify special requirement to deliver the course (e.g: software, nursery, computer lab, simulation room etc) :</th>
                    <td colspan='<?= ($plo_num_col - 1) ?>' style='vertical-align: middle'><?= htmlspecialchars($course["special_requirement"] ?? '') ?></td>
                </tr>

                <!-- Number 12 -->
                <tr>
                    <th>12</th>
                    <th colspan='2'>References (include required and further readings, and should be the most current) :</th>
                    <td colspan='<?= ($plo_num_col - 1) ?>' style='vertical-align: middle'>
                        <?php 
                        $no = 1;
                        foreach ($data['references'] as $ref) {
                            echo $no . ". " . htmlspecialchars($ref["reference_desc"] ?? '') . "<br/>";
                            $no++;
                        }
                        ?>
                    </td>
                </tr>

                <!-- Number 13 -->
                <tr>
                    <th>13</th>
                    <th colspan='2'>Other additional information :</th>
                    <td colspan='<?= ($plo_num_col - 1) ?>'  style='vertical-align: middle'><?= htmlspecialchars($course["other_information"] ?? '') ?></td>
                </tr>
            </tbody>
        </table>
    </body>
</html>