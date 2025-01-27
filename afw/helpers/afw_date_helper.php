<?php
class AfwDateHelper
{
    private static $hijri_date_limit = '14280918';
    private static $jdl = [8761, 8790, 8820, 8849, 8879, 8908, 8937, 8967, 8996, 9026, 9055, 9085, 9115, 9144, 9174, 9204, 9233, 9263, 9292, 9321, 9351, 9380, 9409, 9439, 9469, 9498, 9528, 9558, 9588, 9617, 9647, 9676, 9705, 9735, 9764, 9793, 9823, 9852, 9882, 9912, 9942, 9971, 10001, 10031, 10060, 10089, 10119, 10148, 10177, 10207, 10236, 10266, 10296, 10325, 10355, 10385, 10414, 10444, 10473, 10503, 10532, 10561, 10591, 10620, 10650, 10679, 10709, 10739, 10769, 10798, 10828, 10857, 10887, 10916, 10946, 10975, 11004, 11034, 11063, 11093, 11123, 11152, 11182, 11211, 11241, 11271, 11300, 11330, 11359, 11389, 11418, 11447, 11477, 11506, 11536, 11566, 11595, 11625, 11654, 11684, 11714, 11743, 11773, 11802, 11831, 11861, 11890, 11920, 11949, 11979, 12009, 12038, 12068, 12098, 12127, 12157, 12186, 12215, 12245, 12274, 12303, 12333, 12363, 12392, 12422, 12452, 12482, 12511, 12541, 12570, 12599, 12629, 12658, 12687, 12717, 12746, 12776, 12806, 12836, 12865, 12895, 12925, 12954, 12983, 13013, 13042, 13071, 13101, 13130, 13160, 13190, 13219, 13249, 13279, 13309, 13338, 13367, 13397, 13426, 13455, 13485, 13514, 13544, 13573, 13603, 13633, 13663, 13692, 13722, 13751, 13781, 13810, 13839, 13869, 13898, 13928, 13957, 13987, 14017, 14046, 14076, 14106, 14135, 14165, 14194, 14223, 14253, 14282, 14312, 14341, 14371, 14400, 14430, 14460, 14489, 14519, 14549, 14578, 14608, 14637, 14666, 14696, 14725, 14755, 14784, 14814, 14843, 14873, 14903, 14932, 14962, 14992, 15021, 15051, 15080, 15109, 15139, 15168, 15198, 15227, 15257, 15286, 15316, 15346, 15376, 15405, 15435, 15464, 15493, 15523, 15552, 15581, 15611, 15640, 15670, 15700, 15730, 15759, 15789, 15819, 15848, 15877, 15907, 15936, 15965, 15995, 16024, 16054, 16084, 16113, 16143, 16173, 16203, 16232, 16261, 16291, 16320, 16349, 16379, 16408, 16438, 16467, 16497, 16527, 16557, 16586, 16616, 16645, 16675, 16704, 16733, 16763, 16792, 16822, 16851, 16881, 16911, 16940, 16970, 17000, 17029, 17059, 17088, 17117, 17147, 17176, 17206, 17235, 17265, 17294, 17324, 17354, 17383, 17413, 17443, 17472, 17501, 17531, 17560, 17590, 17619, 17649, 17678, 17708, 17737, 17767, 17797, 17826, 17856, 17885, 17915, 17944, 17974, 18003, 18033, 18062, 18092, 18121, 18151, 18180, 18210, 18240, 18269, 18299, 18329, 18358, 18387, 18417, 18446, 18475, 18505, 18534, 18564, 18594, 18624, 18653, 18683, 18713, 18742, 18771, 18801, 18830, 18859, 18889, 18918, 18948, 18978, 19007, 19037, 19067, 19097, 19126, 19155, 19185, 19214, 19243, 19273, 19302, 19332, 19361, 19391, 19421, 19451, 19480, 19510, 19539, 19569, 19598, 19627, 19657, 19686, 19716, 19745, 19775, 19805, 19835, 19864, 19894, 19923, 19953, 19982, 20011, 20041, 20070, 20100, 20129, 20159, 20188, 20218, 20248, 20278, 20307, 20337, 20366, 20395, 20425, 20454, 20484, 20513, 20543, 20572, 20602, 20632, 20661, 20691, 20720, 20750, 20779, 20809, 20838, 20868, 20897, 20927, 20956, 20986, 21015, 21045, 21075, 21104, 21134, 21163, 21193, 21222, 21252, 21281, 21311, 21340, 21370, 21399, 21429, 21458, 21488, 21518, 21547, 21577, 21606, 21636, 21665, 21695, 21724, 21753, 21783, 21812, 21842, 21872, 21901, 21931, 21961, 21990, 22020, 22049, 22079, 22108, 22137, 22167, 22196, 22226, 22255, 22285, 22315, 22345, 22374, 22404, 22433, 22463, 22492, 22521, 22551, 22580, 22610, 22639, 22669, 22699, 22729, 22758, 22788, 22817, 22847, 22876, 22905, 22935, 22964, 22994, 23023, 23053, 23083, 23112, 23142, 23172, 23201, 23231, 23260, 23289, 23319, 23348, 23378, 23407, 23437, 23466, 23496, 23526, 23555, 23585, 23614, 23644, 23673, 23703, 23732, 23762, 23791, 23821, 23850, 23880, 23909, 23939, 23969, 23998, 24028, 24057, 24087, 24116, 24146, 24175, 24205, 24234, 24264, 24293, 24323, 24352, 24382, 24412, 24441, 24471, 24500, 24530, 24559, 24589, 24618, 24647, 24677, 24706, 24736, 24766, 24795, 24825, 24855, 24884, 24914, 24943, 24973, 25002, 25031, 25061, 25090, 25120, 25149, 25179, 25209, 25239, 25268, 25298, 25327, 25357, 25386, 25415, 25445, 25474, 25504, 25533, 25563, 25593, 25623, 25652, 25682, 25711, 25741, 25770, 25799, 25829, 25858, 25887, 25917, 25947, 25977, 26006, 26036, 26066, 26095, 26125, 26154, 26183, 26213, 26242, 26271, 26301, 26331, 26360, 26390, 26420, 26450, 26479, 26509, 26538, 26567, 26597, 26626, 26655, 26685, 26714, 26744, 26774, 26804, 26833, 26863, 26892, 26922, 26951, 26981, 27010, 27040, 27069, 27098, 27128, 27158, 27187, 27217, 27247, 27276, 27306, 27335, 27365, 27394, 27424, 27453, 27483, 27512, 27541, 27571, 27601, 27630, 27660, 27690, 27719, 27749, 27778, 27808, 27837, 27867, 27896, 27925, 27955, 27984, 28014, 28044, 28073, 28103, 28133, 28162, 28192, 28221, 28251, 28280, 28309, 28339, 28368, 28398, 28427, 28457, 28487, 28517, 28546, 28576, 28605, 28635, 28664, 28693, 28723, 28752, 28781, 28811, 28841, 28870, 28900, 28930, 28960, 28989, 29019, 29048, 29077, 29107, 29136, 29165, 29195, 29225, 29254, 29284, 29314, 29344, 29373, 29403, 29432, 29461, 29491, 29520, 29549, 29579, 29608, 29638, 29668, 29698, 29727, 29757, 29787, 29816, 29845, 29875, 29904, 29933, 29963, 29992, 30022, 30052, 30081, 30111, 30141, 30170, 30200, 30229, 30259, 30288, 30318, 30347, 30376, 30406, 30435, 30465, 30495, 30524, 30554, 30584, 30613, 30643, 30672, 30702, 30731, 30760, 30790, 30819, 30849, 30878, 30908, 30938, 30967, 30997, 31027, 31056, 31086, 31115, 31145, 31174, 31203, 31233, 31262, 31292, 31321, 31351, 31381, 31410, 31440, 31470, 31499, 31529, 31558, 31587, 31617, 31646, 31675, 31705, 31735, 31764, 31794, 31824, 31854, 31883, 31913, 31942, 31971, 32001, 32030, 32059, 32089, 32119, 32148, 32178, 32208, 32238, 32267, 32297, 32326, 32355, 32385, 32414, 32443, 32473, 32502, 32532, 32562, 32592, 32621, 32651, 32681, 32710, 32739, 32769, 32798, 32827, 32857, 32886, 32916, 32946, 32975, 33005, 33035, 33064, 33094, 33123, 33153, 33182, 33211, 33241, 33270, 33300, 33329, 33359, 33389, 33419, 33448, 33478, 33507, 33537, 33566, 33596, 33625, 33654, 33684, 33713, 33743, 33773, 33802, 33832, 33861, 33891, 33921, 33950, 33980, 34009, 34038, 34068, 34097, 34127, 34156, 34186, 34215, 34245, 34275, 34304, 34334, 34364, 34393, 34423, 34452, 34481, 34511, 34540, 34570, 34599, 34629, 34659, 34688, 34718, 34748, 34777, 34807, 34836, 34865, 34895, 34924, 34953, 34983, 35013, 35042, 35072, 35102, 35132, 35161, 35191, 35220, 35249, 35279, 35308, 35337, 35367, 35396, 35426, 35456, 35486, 35515, 35545, 35575, 35604, 35633, 35663, 35692, 35721, 35751, 35780, 35810, 35840, 35869, 35899, 35929, 35958, 35988, 36017, 36047, 36076, 36105, 36135, 36164, 36194, 36223, 36253, 36283, 36313, 36342, 36372, 36401, 36431, 36460, 36489, 36519, 36548, 36578, 36607, 36637, 36667, 36696, 36726, 36756, 36785, 36815, 36844, 36873, 36903, 36932, 36962, 36991, 37021, 37050, 37080, 37110, 37139, 37169, 37198, 37228, 37258, 37287, 37316, 37346, 37375, 37405, 37434, 37464, 37493, 37523, 37553, 37582, 37612, 37642, 37671, 37701, 37730, 37759, 37789, 37818, 37847, 37877, 37907, 37936, 37966, 37996, 38025, 38055, 38085, 38114, 38143, 38173, 38202, 38231, 38261, 38290, 38320, 38350, 38380, 38409, 38439, 38469, 38498, 38527, 38557, 38586, 38615, 38645, 38674, 38704, 38734, 38763, 38793, 38823, 38853, 38882, 38911, 38941, 38970, 38999, 39029, 39058, 39088, 39117, 39147, 39177, 39207, 39236, 39266, 39295, 39325, 39354, 39383, 39413, 39442, 39472, 39501, 39531, 39561, 39590, 39620, 39650, 39679, 39709, 39738, 39767, 39797, 39826, 39856, 39885, 39915, 39944, 39974, 40004, 40033, 40063, 40092, 40122, 40151, 40181, 40210, 40240, 40269, 40299, 40328, 40358, 40387, 40417, 40447, 40476, 40506, 40535, 40565, 40594, 40624, 40653, 40683, 40712, 40742, 40771, 40801, 40830, 40860, 40890, 40919, 40949, 40978, 41008, 41037, 41067, 41096, 41125, 41155, 41184, 41214, 41244, 41274, 41303, 41333, 41363, 41392, 41421, 41451, 41480, 41509, 41539, 41568, 41598, 41628, 41657, 41687, 41717, 41747, 41776, 41805, 41835, 41864, 41893, 41923, 41952, 41982, 42011, 42041, 42071, 42101, 42130, 42160, 42189, 42219, 42248, 42277, 42307, 42336, 42366, 42395, 42425, 42455, 42484, 42514, 42544, 42573, 42603, 42632, 42661, 42691, 42720, 42750, 42779, 42809, 42838, 42868, 42898, 42928, 42957, 42987, 43016, 43045, 43075, 43104, 43134, 43163, 43193, 43222, 43252, 43282, 43311, 43341, 43370, 43400, 43429, 43459, 43488, 43518, 43547, 43577, 43606, 43636, 43665, 43695, 43725, 43754, 43784, 43813, 43843, 43872, 43902, 43931, 43961, 43990, 44019, 44049, 44079, 44108, 44138, 44168, 44197, 44227, 44256, 44286, 44315, 44345, 44374, 44403, 44433, 44462, 44492, 44522, 44551, 44581, 44611, 44640, 44670, 44699, 44729, 44758, 44787, 44817, 44846, 44876, 44905, 44935, 44965, 44995, 45024, 45054, 45083, 45113, 45142, 45171, 45201, 45230, 45260, 45289, 45319, 45349, 45379, 45408, 45438, 45467, 45497, 45526, 45555, 45585, 45614, 45643, 45673, 45703, 45732, 45762, 45792, 45822, 45851, 45881, 45910, 45939, 45969, 45998, 46027, 46057, 46087, 46116, 46146, 46176, 46205, 46235, 46264, 46294, 46323, 46353, 46382, 46412, 46441, 46471, 46500, 46530, 46559, 46589, 46619, 46648, 46678, 46707, 46737, 46766, 46796, 46825, 46855, 46884, 46914, 46943, 46973, 47002, 47032, 47062, 47091, 47121, 47150, 47180, 47209, 47239, 47268, 47297, 47327, 47356, 47386, 47416, 47445, 47475, 47505, 47534, 47564, 47593, 47623, 47652, 47681, 47711, 47740, 47770, 47799, 47829, 47859, 47889, 47918, 47948, 47977, 48007, 48036, 48065, 48095, 48124, 48154, 48183, 48213, 48243, 48273, 48302, 48332, 48361, 48391, 48420, 48449, 48479, 48508, 48537, 48567, 48597, 48626, 48656, 48686, 48716, 48745, 48775, 48804, 48833, 48863, 48892, 48921, 48951, 48981, 49010, 49040, 49070, 49099, 49129, 49159, 49188, 49217, 49247, 49276, 49305, 49335, 49364, 49394, 49424, 49453, 49483, 49513, 49542, 49572, 49601, 49631, 49660, 49690, 49719, 49748, 49778, 49808, 49837, 49867, 49897, 49926, 49956, 49985, 50015, 50044, 50074, 50103, 50132, 50162, 50191, 50221, 50251, 50280, 50310, 50339, 50369, 50399, 50428, 50458, 50487, 50517, 50546, 50575, 50605, 50634, 50664, 50693, 50723, 50753, 50783, 50812, 50842, 50871, 50901, 50930, 50959, 50989, 51018, 51048, 51077, 51107, 51137, 51166, 51196, 51226, 51255, 51285, 51314, 51344, 51373, 51402, 51432, 51461, 51491, 51521, 51551, 51581, 51610, 51640, 51669, 51698, 51728, 51757, 51786, 51815, 51845, 51875, 51905, 51935, 51964, 51994, 52024, 52053, 52082, 52112, 52141, 52170, 52199, 52229, 52259, 52289, 52318, 52348, 52378, 52407, 52437, 52466, 52496, 52525, 52554, 52584, 52613, 52643, 52672, 52702, 52732, 52761, 52791, 52821, 52850, 52880, 52909, 52938, 52968, 52997, 53027, 53056, 53086, 53115, 53145, 53175, 53204, 53234, 53263, 53293, 53323, 53352, 53382, 53411, 53440, 53470, 53499, 53529, 53558, 53588, 53618, 53647, 53677, 53707, 53736, 53766, 53795, 53824, 53854, 53883, 53912, 53942, 53972, 54002, 54031, 54061, 54091, 54120, 54150, 54179, 54208, 54238, 54267, 54296, 54326, 54356, 54386, 54415, 54445, 54475, 54504, 54534, 54563, 54592, 54622, 54651, 54680, 54710, 54740, 54769, 54799, 54829, 54858, 54888, 54918, 54947, 54976, 55006, 55035, 55065, 55094, 55124, 55153, 55183, 55212, 55242, 55272, 55301, 55331, 55360, 55390, 55419, 55449, 55478, 55507, 55537, 55566, 55596, 55626, 55656, 55685, 55715, 55744, 55774, 55803, 55833, 55862, 55891, 55921, 55950, 55980, 56010, 56039, 56069, 56099, 56128, 56158, 56187, 56217, 56246, 56275, 56305, 56334, 56364, 56393, 56423, 56453, 56482, 56512, 56542, 56571, 56600, 56630, 56659, 56689, 56718, 56748, 56777, 56807, 56836, 56866, 56896, 56925, 56955, 56984, 57014, 57043, 57073, 57102, 57132, 57161, 57191, 57220, 57250, 57279, 57309, 57339, 57368, 57398, 57428, 57457, 57486, 57516, 57545, 57575, 57604, 57633, 57663, 57693, 57722, 57752, 57782, 57812, 57841, 57870, 57900, 57929, 57958, 57988, 58017, 58047, 58076, 58106, 58136, 58166, 58195, 58225, 58254, 58284, 58313, 58342, 58372, 58401, 58431, 58460, 58490, 58520, 58550, 58579, 58609, 58638, 58668, 58697, 58726, 58756, 58785, 58815, 58844, 58874, 58904, 58933, 58963, 58993, 59022, 59052, 59081, 59110, 59140, 59169, 59199, 59228, 59258, 59287, 59317, 59347, 59376, 59406, 59435, 59465, 59494, 59524, 59553, 59583, 59612, 59642, 59671, 59701, 59730, 59760, 59790, 59819, 59849, 59878, 59908, 59938, 59967, 59996, 60026, 60055, 60085, 60114, 60144, 60173, 60203, 60233, 60263, 60292, 60322, 60351, 60380, 60410, 60439, 60468, 60498, 60527, 60557, 60587, 60617, 60647, 60676, 60706, 60735, 60764, 60794, 60823, 60852, 60882, 60911, 60941, 60971, 61001, 61030, 61060, 61089, 61119, 61148, 61178, 61207, 61236, 61266, 61295, 61325, 61355, 61384, 61414, 61444, 61473, 61503, 61532, 61562, 61591, 61620, 61650, 61679, 61709, 61738, 61768, 61798, 61827, 61857, 61887, 61916, 61946, 61975, 62005, 62034, 62063, 62093, 62122, 62152, 62181, 62211, 62241, 62270, 62300, 62330, 62359, 62389, 62418, 62447, 62477, 62506, 62536, 62565, 62595, 62624, 62654, 62684, 62714, 62743, 62773, 62802, 62831, 62861, 62890, 62920, 62949, 62979, 63008, 63038, 63068, 63098, 63127, 63156, 63186, 63215, 63245, 63274, 63304, 63333, 63362, 63392, 63422, 63452, 63481, 63511, 63540, 63570, 63599, 63629, 63658, 63688, 63717, 63746, 63776, 63806, 63835, 63865, 63894, 63924, 63954, 63983, 64013, 64042, 64072, 64101, 64130, 64160, 64189, 64219, 64248, 64278, 64308, 64338, 64367, 64397, 64426, 64456, 64485, 64514, 64544, 64573, 64602, 64632, 64662, 64691, 64721, 64751, 64781, 64810, 64840, 64869, 64898, 64928, 64957, 64986, 65016, 65046, 65075, 65105, 65135, 65165, 65194, 65224, 65253, 65282, 65312, 65341, 65370, 65400, 65430, 65459, 65489, 65519, 65548, 65578, 65607, 65637, 65666, 65696, 65725, 65754, 65784, 65814, 65843, 65873, 65902, 65932, 65962, 65991, 66021, 66050, 66080, 66109, 66139, 66168, 66198, 66227, 66257, 66286, 66316, 66345, 66375, 66404, 66434, 66464, 66493, 66523, 66552, 66582, 66611, 66640, 66670, 66699, 66729, 66759, 66788, 66818, 66848, 66877, 66907, 66936, 66966, 66995, 67024, 67054, 67083, 67113, 67142, 67172, 67202, 67232, 67261, 67291, 67320, 67350, 67379, 67408, 67438, 67467, 67496, 67526, 67556, 67586, 67616, 67645, 67675, 67704, 67734, 67763, 67792, 67822, 67851, 67881, 67910, 67940, 67970, 67999, 68029, 68059, 68088, 68118, 68147, 68176, 68206, 68235, 68265, 68294, 68324, 68353, 68383, 68413, 68442, 68472, 68501, 68531, 68560, 68590, 68619, 68649, 68678, 68707, 68737, 68767, 68796, 68826, 68856, 68885, 68915, 68945, 68974, 69003, 69033, 69062, 69091, 69121, 69151, 69180, 69210, 69239, 69269, 69299, 69329, 69358, 69387, 69417, 69446, 69475, 69505, 69534, 69564, 69594, 69623, 69653, 69683, 69712, 69742, 69771, 69801, 69830, 69860, 69889, 69918, 69948, 69977, 70007, 70037, 70066, 70096, 70125, 70155, 70185, 70214, 70244, 70273, 70302, 70332, 70361, 70391, 70420, 70450, 70480, 70509, 70539, 70569, 70598, 70628, 70657, 70686, 70716, 70745, 70775, 70804, 70834, 70863, 70893, 70923, 70953, 70982, 71012, 71041, 71070, 71100, 71129, 71158, 71188, 71217, 71247, 71277, 71307, 71336, 71366, 71396, 71425, 71454, 71484, 71513, 71542, 71572, 71601, 71631, 71661, 71690, 71720, 71750, 71780, 71809, 71838, 71868, 71897, 71926, 71956, 71985, 72015, 72045, 72074, 72104, 72134, 72163, 72193, 72222, 72252, 72281, 72310, 72340, 72369, 72399, 72428, 72458, 72488, 72517, 72547, 72576, 72606, 72636, 72665, 72694, 72724, 72753, 72783, 72812, 72842, 72871, 72901, 72930, 72960, 72990, 73019, 73049, 73079, 73108, 73138, 73167, 73196, 73226, 73255, 73285, 73314, 73344, 73374, 73403, 73433, 73463, 73492, 73522, 73551, 73580, 73610, 73639, 73668, 73698, 73728, 73757, 73787, 73817, 73847, 73876, 73906, 73935, 73964, 73994, 74023, 74052, 74082, 74112, 74141, 74171, 74201, 74230, 74260, 74290, 74319, 74348, 74378, 74407, 74436, 74466, 74496, 74525, 74555, 74585, 74614, 74644, 74674, 74703, 74732, 74762, 74791, 74821, 74850, 74880, 74909, 74939, 74968, 74998, 75028, 75057, 75087, 75116, 75146, 75175, 75205, 75234, 75263, 75293, 75322, 75352, 75382, 75411, 75441, 75471, 75500, 75530, 75559, 75589, 75618, 75647, 75677, 75706, 75736, 75765, 75795, 75825, 75855, 75884, 75914, 75943, 75973, 76002, 76031, 76061, 76090, 76120, 76149, 76179, 76209, 76238, 76268, 76298, 76327, 76357, 76386, 76415, 76445, 76474, 76504, 76533, 76563, 76592, 76622, 76652, 76681, 76711, 76740, 76770, 76800, 76829, 76858, 76888, 76917, 76947, 76976, 77006, 77035, 77065, 77095, 77124, 77154, 77184, 77213, 77242, 77272, 77301, 77331, 77360, 77389, 77419, 77449, 77478, 77508, 77538, 77568, 77597, 77626, 77656, 77685, 77714, 77744, 77773, 77803, 77832, 77862, 77892, 77922, 77951, 77981, 78010, 78040, 78069, 78098, 78128, 78157, 78187, 78216, 78246, 78276, 78306, 78335, 78365, 78394, 78424, 78453, 78482, 78512, 78541, 78571, 78600, 78630, 78660, 78689, 78719, 78749, 78778, 78807, 78837, 78866, 78896, 78925, 78955, 78984, 79014, 79043, 79073, 79103, 79132, 79162, 79191, 79221, 79250, 79280, 79309, 79339, 79368, 79398, 79427, 79457, 79486, 79516, 79545, 79575, 79605, 79634, 79664, 79694, 79723, 79752, 79782, 79811, 79840, 79870, 79899, 79929, 79959];
    private static $uF = ['en' => [1 => 'Muharram', 'Safar', "Rabi' I", "Rabi' II", 'Jumada I', 'Jumada II', 'Rajab', "Sha'aban", 'Ramadan', 'Shawwal', "Dhu al-Qi'dah", 'Dhu al-Hijjah'], 'ar' => [1 => 'محرّم', 'صفر', 'ربيع الأول', 'ربيع الآخر', 'جمادى الأول', 'جمادى الآخر', 'رجب', 'شعبان', 'رمضان', 'شوّال', 'ذو القعدة', 'ذو الحجة']];
    private static $uM = ['en' => [1 => 'Muh', 'Saf', 'Rb1', 'Rb2', 'Jm1', 'Jm2', 'Raj', 'Shb', 'Rmd', 'Shw', 'DhQ', 'DhH'], 'ar' => [1 => 'مح', 'صف', 'ر1', 'ر2', 'ج1', 'ج2', 'رج', 'شع', 'رم', 'شو', 'ذق', 'ذح']];
    private static $D = ['ar' => ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت']];
    private static $l = ['ar' => ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']];
    private static $F = [
        'ar' => [1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'],
        'en' => [1 => 'January', 'February', 'March', 'April', 'mayo', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                        ];

    private static $M = ['ar' => [1 => 'كانون الثاني', 'شباط', 'آذار', 'نيسان', 'أيار', 'حزيران', 'تموز', 'آب', 'أيلول', 'تشرين الأول', 'تشرين الثاني', 'كانون الأول']];
    private static $a = ['ar' => ['am' => 'ص', 'pm' => 'م']];
    private static $A = ['ar' => ['AM' => 'صباحاً', 'PM' => 'مساءً']];



    /**
     * @param string format     date format (see http://php.net/manual/en/function.date.php)
     *         Integer timestamp   time measured in the number of seconds since
     *                        the Unix Epoch (January 1 1970 00:00:00 GMT)
     *         Integer hijri   boolean defines whether you want to get Hijri or Gregorian date
     * @param int $timestamp
     * @param int $hijri
     *
     * @return string Returns the Hijri/Greg date according to format and timestamp in Arabic/English
     *                string date string [format , int timestamp, int hijri (0 or 1)]
     * @desc   date returns a string formatted according to the given format string using the given
     *                  integer timestamp or the current time if no timestamp is given. In other words, timestamp
     *                  is optional and defaults to the value of time(), also hijri can be set to 0 to get Greg date.
     */
    public static function Date($format = "Ymd", $timestamp = 0, $lang = "ar", $hijri = 1)
    {
        //if ($timestamp === 0) $timestamp = time();
        if ($timestamp === 0) {
            $timestamp = time();
        } else {
            $timestamp = strtotime($timestamp);
        }
        list($d, $D, $j, $l, $S, $F, $m, $M, $n, $t, $L, $o, $Y, $y, $w, $a, $A, $H, $i, $s, $O) = explode('/', date('d/D/j/l/S/F/m/M/n/t/L/o/Y/y/w/a/A/H/i/s/O', $timestamp));
        if ($hijri) {
            extract(static::convertGregorianToHijriArray($d, $m, $Y));
            $j = $day;
            $t = $ml;
            $L = $ln;
            $d = sprintf('%02d', $day);
            $m = sprintf('%02d', $month);
            $n = $month;
            $Y = $year;
            $y = substr($year, 2);
            $S = substr($j, -1) == 1 ? 'st' : (substr($j, -1) == 2 ? 'nd' : (substr($j, -1) == 3 ? 'rd' : 'th'));
            if ($lang == 'ar') {
                $F = self::$uF[$lang][$n];
                $M = self::$uM[$lang][$n];
            } else {
                $F = self::$uF[$lang][$n];
                $M = self::$uM[$lang][$n];
            }
        } else {
            if ($lang == 'ar') {
                $F = self::$F[$lang][$n];
                $M = self::$M[$lang][$n];
            }
        }
        if ($lang == 'ar') {
            $D = self::$D[$lang][$w];
            $l = self::$l[$lang][$w];
            $S = '';
            $a = self::$a[$lang][$a];
            $A = self::$A[$lang][$A];
        }
        $Y = $Y;
        $r = "$D, $j $M $Y $H:$i:$s $O";
        $davars = ['d', 'D', 'j', 'l', 'S', 'F', 'm', 'M', 'n', 't', 'L', 'o', 'Y', 'y', 'a', 'A', 'r'];
        $myvars = [$d, '¢', $j, '£', 'ç', '¥', $m, '©', $n, $t, $L, $Y, $Y, $y, 'ï', 'â', '®'];
        //dd($myvars);
        $format = str_replace($davars, $myvars, $format);
        $date = date($format, $timestamp);
        $date = str_replace(['¢', '£', 'ç', '¥', '©', 'ï', 'â', '®'], [$D, $l, $S, $F, $M, $a, $A, $r], $date);
        return $date;
    }

    public static function getFullMonthName($n, $lang)
    {
        return self::$F[$lang][$n];
    }

    public static function DateIndicDigits($format, $timestamp = 0)
    {
        return self::TransformToIndianNumbers(self::Date($format, $timestamp));
    }

    public static function ShortDate($timestamp = 0)
    {
        $format = 'Y/m/d';

        return self::Date($format, $timestamp);
    }

    public static function ShortDateIndicDigits($timestamp = 0)
    {
        $format = 'Y/m/d';

        return self::DateIndicDigits($format, $timestamp);
    }

    public static function MediumDate($timestamp = 0)
    {
        $format = 'l ، j F ، Y';

        return self::Date($format, $timestamp);
    }

    public static function MediumDateIndicDigits($timestamp = 0)
    {
        $format = 'l ، j F ، Y';

        return self::DateIndicDigits($format, $timestamp);
    }

    public static function FullDate($timestamp = 0)
    {
        $format = 'l ، j F ، Y - h:i:s A';

        return self::Date($format, $timestamp);
    }

    public static function FullDateIndicDigits($timestamp = 0)
    {
        $format = 'l ، j F ، Y - h:i:s A';

        return self::DateIndicDigits($format, $timestamp);
    }

    /**
     * @param int $day
     * @param int $month
     * @param int $year
     *
     * @return array Hijri date [int month, int day, int year, int ln, int ml]
     * @desc   setFromGregorianDMY() returns an array of  month, day, year, ln: which is "Islamic lunation number
     *                  (Births of New Moons)", int: The length of current month.
     * @thanks to Robert Gent method maker (http://www.phys.uu.nl/~vgent/islam/ummalqura.htm)
     */
    public static function convertGregorianToHijriArray($day = 20, $month = 02, $year = 2030)
    {
        $jd = gregoriantojd($month, $day, $year);
        $mjd = $jd - 2400000;
        foreach (static::$jdl as $i => $v) {
            if ($v > ($mjd - 1)) {
                break;
            }
        }
        $iln = $i + 15588; // Islamic lunation number (Births of New Moons)
        $ii = floor(($i - 1) / 12);
        $y = 1300 + $ii; // year
        $m = $i - 12 * $ii; // month
        $d = $mjd - static::$jdl[$i - 1]; //day
        $ml = static::$jdl[$i] - static::$jdl[$i - 1]; // Month Length
        list($id['month'], $id['day'], $id['year'], $id['ln'], $id['ml']) = explode('/', "$m/$d/$y/$iln/$ml");
        //dd($id);
        return $id;
    }


    public static function format2digits($a)
    {
        return str_pad($a, 2, "0", STR_PAD_LEFT);
    }

    public static function convertGregorianToHijri($day = 20, $month = 02, $year = 2030, $format = "Ymd", $indianNumbers = false)
    {
        $dateArray = self::convertGregorianToHijriArray($day, $month, $year);

        $arr_from = ["Y", "m", "d"];
        $dateArray['month'] = self::format2digits($dateArray['month']);
        $dateArray['day'] = self::format2digits($dateArray['day']);

        $arr_to = [$dateArray['year'], $dateArray['month'], $dateArray['day']];
        $return = str_replace($arr_from, $arr_to, $format);
        if ($indianNumbers) $return = self::TransformToIndianNumbers($return);
        return $return;
    }

    public static function convertGregToHijri($gdate, $format = "Ymd", $indianNumbers = false)
    {
        list($year, $month, $day) = self::splitGregDate($gdate, true);
        return self::convertGregorianToHijri($day, $month, $year, $format, $indianNumbers);
    }

    /**
     * @param int $day
     * @param int $month
     * @param int $year
     *
     * @return array Gregorian date [int month, int day, int year, int ln, int ml]
     * @desc   u2g() converts from Hijri to Gregorian date returns an array of  month, day,
     *                    year, ln: which is "Islamic lunation number (Births of New Moons)",
     *                    int: The length of current month.
     */
    public static function convertHijriToGregorianArray($day = 20, $month = 02, $year = 1447)
    {
        $ii = $year - 1300;
        $i = $month + 12 * $ii;
        $mjd = $day + static::$jdl[$i - 1];
        $ml = static::$jdl[$i] - static::$jdl[$i - 1];
        $iln = $i + 15588; // Islamic lunation number (Births of New Moons)
        $jd = $mjd + 2400000;
        list($g['month'], $g['day'], $g['year'], $g['ln'], $g['ml']) = explode('/', jdtogregorian($jd) . "/$iln/$ml");
        $g['month'] = intval($g['month']);
        $g['day'] = intval($g['day']);

        return $g;
    }

    public static function convertHijriToGregorian($day = 20, $month = 02, $year = 1447, $format = "Y-m-d")
    {
        $dateArray = self::convertHijriToGregorianArray($day, $month, $year);
        $dateArray['month'] = self::format2digits($dateArray['month']);
        $dateArray['day'] = self::format2digits($dateArray['day']);

        $arr_from = ["Y", "m", "d"];
        $arr_to = [$dateArray['year'], $dateArray['month'], $dateArray['day']];
        return str_replace($arr_from, $arr_to, $format);
    }

    public static function convertHijriToGreg($hdate, $indianNumbers = true, $format = "Y-m-d")
    {
        list($year, $month, $day) = self::splitHijriDate($hdate, true);
        if ($indianNumbers) $year = self::TransformFromIndianNumbers($year);
        if ($indianNumbers) $month = self::TransformFromIndianNumbers($month);
        if ($indianNumbers) $day = self::TransformFromIndianNumbers($day);
        return self::convertHijriToGregorian($day, $month, $year, $format);
    }



    public static function TransformToIndianNumbers($value)
    {
        if (is_string($value)) {
            $arabic_eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $arabic_western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

            return str_replace($arabic_western, $arabic_eastern, $value);
        }

        return $value;
    }

    public static function TransformFromIndianNumbers($value)
    {
        if (is_string($value)) {
            $arabic_eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $arabic_western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

            return str_replace($arabic_eastern, $arabic_western, $value);
        }

        return $value;
    }


    /***************************************************************************** */
    private static $MIN_GREG_YEAR = 1000;
    private static $MAX_GREG_YEAR = 2999;

    private static $MIN_HIJRI_YEAR = 1000;
    private static $MAX_HIJRI_YEAR = 1999;

    private static $englishToArabicGregMonths =
    [
        'JANUARY' =>        'يناير',
        'FEBRUARY' =>               'فبراير',
        'MARCH' =>                  'مارس',
        'APRIL' =>                  'أبريل',
        'MAYO' =>                   'مايو',
        'JUNE' =>                   'يونيو',
        'JULY' =>                   'يوليو',
        'AUGUST' =>                 'أغسطس',
        'SEPTEMBER' =>              'سبتمبر',
        'OCTOBER' =>                'أكتوبر',
        'NOVEMBER' =>               'نوفمبر',
        'DECEMBER' =>               'ديسمبر',
    ];




    private static $enGregMonths = [
        'January',
        'February',
        'March',
        'April',
        'mayo',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December',
    ];

    private static $gregMonths = [
        'يناير',
        'فبراير',
        'مارس',
        'أبريل',
        'مايو',
        'يونيو',
        'يوليو',
        'أغسطس',
        'سبتمبر',
        'أكتوبر',
        'نوفمبر',
        'ديسمبر',
    ];

    private static $hijMonths = [
        'محرم',
        'صفر',
        'ربيع الأول',
        'ربيع الآخر',
        'جمادى الأولى',
        'جمادى الآخرة',
        'رجب',
        'شعبان',
        'رمضان',
        'شوّال',
        'ذو القعدة',
        'ذو الحجة',
    ];

    private static $weekDays = [
        'الأحد',
        'الأثنين',
        'الثلاثاء',
        'الإربعاء',
        'الخميس',
        'الجمعة',
        'السبت',
    ];

    private static $shortWeekDays = [
        'أحد',
        'أثنين',
        'ثلاثاء',
        'إربعاء',
        'خميس',
        'جمعة',
        'سبت',
    ];

    public static function dateToTimestamp($date)
    {
        $arr_dat = explode(' ', $date);
        $arr_day = explode('-', $arr_dat[0]);
        $arr_hour = explode(':', $arr_dat[1]);
        if (!$arr_hour[0]) {
            $arr_hour[0] = 0;
        }
        if (!$arr_hour[1]) {
            $arr_hour[1] = 0;
        }
        if (!$arr_hour[2]) {
            $arr_hour[2] = 0;
        }
        $tmstmp = mktime(
            $arr_hour[0],
            $arr_hour[1],
            $arr_hour[2],
            $arr_day[1],
            $arr_day[2],
            $arr_day[0]
        );

        return $tmstmp;
    }

    public static function weekDayNum($wanted_week_day)
    {
        if (is_numeric($wanted_week_day)) {
            return $wanted_week_day;
        }
        if (strtolower($wanted_week_day) == 'sunday') {
            return 0;
        }
        if (strtolower($wanted_week_day) == 'monday') {
            return 1;
        }
        if (strtolower($wanted_week_day) == 'tuesday') {
            return 2;
        }
        if (strtolower($wanted_week_day) == 'wednesday') {
            return 3;
        }
        if (strtolower($wanted_week_day) == 'thursday') {
            return 4;
        }
        if (strtolower($wanted_week_day) == 'friday') {
            return 5;
        }
        if (strtolower($wanted_week_day) == 'saturday') {
            return 6;
        }

        return $wanted_week_day;
    }

    public static function dayNameForDate($date_greg, $translate_lang = '')
    {
        $php_day_of_week = self::weekDayOf($date_greg);

        return self::dayNameOfDayNum($php_day_of_week, $translate_lang);
    }

    public static function nameDayTranslate($day_en_mame, $translate_lang = '')
    {
        global $lang;
        if (!$translate_lang) $translate_lang = $lang;
        if (!$translate_lang) $translate_lang = "ar";
        $php_day_of_week = self::weekDayNum($day_en_mame);
        return self::dayNameOfDayNum($php_day_of_week, $translate_lang);
    }

    public static function nameMonthTranslate($month_en_mame, $translate_lang = '')
    {
        global $lang;
        if (!$translate_lang) $translate_lang = $lang;
        if (!$translate_lang) $translate_lang = "ar";
        if ($translate_lang != "ar") return $month_en_mame;
        return self::$englishToArabicGregMonths[strtoupper(trim($month_en_mame))];
    }

    public static function dayNameOfDayNum($php_day_of_week, $translate_lang = '')
    {
        // die("ss : $date_greg > $tms_dep > w = $day_of_week");
        if (!$translate_lang) {
            return $php_day_of_week;
        }

        $days_title_arr = [];
        $days_title_arr[1] = [
            'ar' => 'الأحد',
            'en' => 'sunday',
            'fr' => 'dimanche',
        ];
        $days_title_arr[2] = [
            'ar' => 'الاثنين',
            'en' => 'monday',
            'fr' => 'lundi',
        ];
        $days_title_arr[3] = [
            'ar' => 'الثلاثاء',
            'en' => 'tuesday',
            'fr' => 'mardi',
        ];
        $days_title_arr[4] = [
            'ar' => 'الاربعاء',
            'en' => 'wednesday',
            'fr' => 'mercredi',
        ];
        $days_title_arr[5] = [
            'ar' => 'الخميس',
            'en' => 'thursday',
            'fr' => 'jeudi',
        ];
        $days_title_arr[6] = [
            'ar' => 'الجمعة',
            'en' => 'friday',
            'fr' => 'vendredi',
        ];
        $days_title_arr[7] = [
            'ar' => 'السبت',
            'en' => 'saturday',
            'fr' => 'samedi',
        ];

        return $days_title_arr[$php_day_of_week + 1][$translate_lang];
    }

    /**
     *
     * return next coming week day ex Thursday after the date $from_date
     *
     */

    public static function nextWeekDayDate(
        $from_date = '',
        $wanted_week_day = 4
    ) {
        if (!$from_date) {
            $from_date = date('Y-m-d');
        }
        $auj = self::dateToTimestamp($from_date);
        $n = date('w', $auj);
        //if($n>0) $offset = 0; else $offset = -7;
        $previous_sunday = date('d', $auj) - $n;
        if ($n > 0) {
            $sunday = $previous_sunday + 7;
        } else {
            $sunday = $previous_sunday;
        }
        $wanted_week_day = self::weekDayNum($wanted_week_day);

        $wanted_day = $sunday + $wanted_week_day;
        $next_week_day = mktime(
            0,
            0,
            0,
            date('m', $auj),
            $wanted_day,
            date('Y', $auj)
        );
        $next_week_day_date = date('Y-m-d', $next_week_day);
        //die("from_date=$from_date, wanted_week_day=$wanted_week_day, sunday=$sunday, wanted_day=$wanted_day, next_week_day_date=$next_week_day_date");
        return $next_week_day_date;
    }

    public static function inputFormatHijriDate($hdate)
    {
        return implode(
            '-',
            self::splitHijriDate(self::repareHijriDate($hdate))
        );
    }

    public static function inputFormatDate($gdate)
    {
        if ((!$gdate) or ($gdate == '0000-00-00')) {
            return '';
        }
        return implode('-', self::splitGregDate($gdate));
    }

    public static function isCorrectHijriDate($hdate)
    {
        try {
            self::splitHijriDate($hdate, $convertToInt = true);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function isCorrectGregDate($gdate)
    {
        try {
            self::splitGregDate($gdate, $convertToInt = true);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function splitHijriDate($hdate, $convertToInt = false)
    {
        if (strlen($hdate) != 8 or !is_numeric($hdate)) {
            throw new AfwRuntimeException(
                "hijri date '$hdate' is not formatted correctly use format YYYYMMDD without '-' neither '/' nor any separator"
            );
        }
        $hdate_YYYY = substr($hdate, 0, 4);
        $hdate_MM = substr($hdate, 4, 2);
        $hdate_DD = substr($hdate, 6, 2);

        if (is_numeric($hdate_YYYY)) {
            $yyyy = intval($hdate_YYYY);
        } else {
            $yyyy = -1;
        }
        if ($yyyy < self::$MIN_HIJRI_YEAR or $yyyy > self::$MAX_HIJRI_YEAR) {
            throw new AfwRuntimeException(
                "hijri date '$hdate' is not formatted correctly use format YYYYMMDD, incorrect year $hdate_YYYY"
            );
        }
        if (is_numeric($hdate_MM)) {
            $mm = intval($hdate_MM);
        } else {
            $mm = -1;
        }
        if ($mm < 1 or $mm > 12) {
            throw new AfwRuntimeException(
                "hijri date '$hdate' is not formatted correctly use format YYYYMMDD, incorrect month $hdate_MM"
            );
        }

        if (is_numeric($hdate_DD)) {
            $dd = intval($hdate_DD);
        } else {
            $dd = -1;
        }
        if ($dd < 1 or $dd > 30) {
            throw new AfwRuntimeException(
                "hijri date '$hdate' is not formatted correctly use format YYYYMMDD, incorrect day $hdate_DD"
            );
        }

        if ($convertToInt) {
            $hdate_YYYY = $yyyy;
            $hdate_MM = $mm;
            $hdate_DD = $dd;
        }

        return [$hdate_YYYY, $hdate_MM, $hdate_DD];
    }

    public static function splitGregDate($gdate, $convertToInt = false)
    {
        $return = explode('-', $gdate);

        if (count($return) != 3) {
            throw new AfwRuntimeException(
                "gregorian date '$gdate' is not formatted correctly use format YYYY-MM-DD"
            );
        }

        $yyyy = intval($return[0]);
        if ($yyyy < self::$MIN_GREG_YEAR or $yyyy > self::$MAX_GREG_YEAR) {
            throw new AfwRuntimeException(
                "greg date '$gdate' is not formatted correctly use format YYYY-MM-DD, incorrect year"
            );
        }
        $mm = intval($return[1]);
        if ($mm < 1 or $mm > 12) {
            throw new AfwRuntimeException(
                "greg date '$gdate' is not formatted correctly use format YYYY-MM-DD, incorrect month"
            );
        }

        $dd = intval($return[2]);
        if ($dd < 1 or $dd > 31) {
            throw new AfwRuntimeException(
                "greg date '$gdate' is not formatted correctly use format YYYY-MM-DD, incorrect day"
            );
        }

        if ($convertToInt) {
            $return[0] = $yyyy;
            $return[1] = $mm;
            $return[2] = $dd;
        }

        return $return;
    }

    public static function shortHijriDate($hdate)
    {
        return self::formatHijriDate(
            $hdate,
            $format = [
                'separator' => ' ',
                'month_name' => false,
                'show_day_name' => true,
                'short_day_name' => true,
                'show_year' => false,
                'show_month' => false,
            ]
        );
    }

    public static function mediumHijriDate($hdate)
    {
        return self::formatHijriDate(
            $hdate,
            $format = [
                'separator' => ' ',
                'month_name' => true,
                'show_day_name' => true,
                'short_day_name' => false,
                'show_year' => false,
                'show_month' => true,
            ]
        );
    }

    public static function fullHijriDate($hdate)
    {
        return self::formatHijriDate(
            $hdate,
            $format = [
                'separator' => ' ',
                'month_name' => true,
                'show_day_name' => true,
                'short_day_name' => false,
                'show_year' => true,
                'show_month' => true,
            ]
        );
    }

    public static function longHijriDate($hdate)
    {
        return self::formatHijriDate($hdate);
    }

    public static function formatHijriDate(
        $hdate,
        $format = [
            'separator' => ' ',
            'month_name' => true,
            'show_day_name' => true,
            'short_day_name' => false,
            'show_year' => true,
            'show_month' => true,
        ]
    ) {
        if (is_array($hdate)) {
            list($hijri_year, $hijri_month, $hijri_day) = $hdate;
        } else {
            list($hijri_year, $hijri_month, $hijri_day) = self::splitHijriDate(
                self::repareHijriDate($hdate)
            );
        }

        $weekday = self::weekDayOfHijriDate($hdate);
        $myDateFinal = '';

        if ($format['show_day_name']) {
            if ($format['short_day_name']) {
                $day_name = self::$shortWeekDays[$weekday];
            } else {
                $day_name = self::$weekDays[$weekday];
            }
            $myDateFinal .= $format['separator'] . $day_name;
        }
        $myDateFinal .= $format['separator'] . $hijri_day;
        if ($format['show_month']) {
            if ($format['month_name']) {
                $myDateFinal .=
                    $format['separator'] .
                    self::$hijMonths[intval($hijri_month) - 1];
            } else {
                $myDateFinal .= $format['separator'] . $hijri_month;
            }
        }

        if ($format['show_year']) {
            $myDateFinal .= $format['separator'] . $hijri_year;
        }

        return trim($myDateFinal, $format['separator']);
    }

    public static function shortGregDate($gdate)
    {
        return self::formatGregDate(
            $gdate,
            $format = [
                'separator' => ' ',
                'month_name' => false,
                'show_day_name' => true,
                'short_day_name' => true,
                'show_year' => false,
                'show_month' => false,
            ]
        );
    }

    public static function mediumGregDate($gdate)
    {
        return self::formatGregDate(
            $gdate,
            $format = [
                'separator' => ' ',
                'month_name' => true,
                'show_day_name' => true,
                'short_day_name' => false,
                'show_year' => false,
                'show_month' => true,
            ]
        );
    }

    public static function fullGregDate($gdate, $format_customized = array())
    {
        $format = [
            'separator' => ' ',
            'month_name' => true,
            'show_day_name' => true,
            'short_day_name' => false,
            'show_year' => true,
            'show_month' => true,
        ];

        foreach ($format_customized as $key => $val) {
            $format[$key] = $val;
        }

        return self::formatGregDate($gdate, $format);
    }

    public static function longGregDate($gdate)
    {
        return self::formatGregDate($gdate);
    }

    public static function formatGregDate(
        $gdate,
        $format = [
            'separator' => ' ',
            'month_name' => true,
            'show_day_name' => true,
            'short_day_name' => false,
            'show_year' => true,
            'show_month' => true,
        ]
    ) {
        // remove time if exists
        list($gdate, $gtime) = explode(' ', $gdate);

        if (is_array($gdate)) {
            list($greg_year, $greg_month, $greg_day) = $gdate;
        } else {
            list($greg_year, $greg_month, $greg_day) = self::splitGregDate(
                $gdate
            );
        }

        $weekday = self::weekDayOf($gdate);
        $myDateFinal = '';

        if ($format['show_day_name']) {
            if ($format['short_day_name']) {
                $day_name = self::$shortWeekDays[$weekday];
            } else {
                $day_name = self::$weekDays[$weekday];
            }
            $myDateFinal .= $format['separator'] . $day_name;
        }
        $myDateFinal .= $format['separator'] . $greg_day;
        if ($format['show_month']) {
            if ($format['month_name']) {
                $myDateFinal .=
                    $format['separator'] .
                    self::$gregMonths[intval($greg_month) - 1];
            } else {
                $myDateFinal .= $format['separator'] . $greg_month;
            }
        }

        if ($format['show_year']) {
            $myDateFinal .= $format['separator'] . $greg_year;
        }

        return trim($myDateFinal, $format['separator']);
    }


    public static function addHijriPeriodToHijriDate($hdate, $nb_months, $nb_years = 0)
    {
        if (strpos($hdate, '-') === false) {
            $hdate = self::add_dashes($hdate);
        }

        $hd_arr = explode('-', $hdate);
        $v_y1 = intval($hd_arr[0]);
        $v_m1 = intval($hd_arr[1]);
        $v_d1 = intval($hd_arr[2]);

        $v_m1 += $nb_months;

        if ($v_m1 > 12) {
            $v_m2 = $v_m1;
            $v_m1 = $v_m2 % 12;
            $nb_years_added = floor($v_m2 / 12);

            $v_y1 += $nb_years_added;
        }

        $v_y1 += $nb_years;

        $mm = str_pad($v_m1, 2, "0", STR_PAD_LEFT);
        $dd = str_pad($v_d1, 2, '0', STR_PAD_LEFT);

        return $v_y1 . $mm . $dd;
    }

    public static function genereHijriDates(
        $from_date,
        $to_date,
        $increment_hmonths = 0,
        $increment_hyears = 0,
        $calc_greg = true,
        $gen_desc = false
    ) {
        $my_date = $from_date;

        $arr_hij_period = [];
        if ($increment_hmonths + $increment_hyears > 0) {
            while ($my_date <= $to_date) {
                if ($gen_desc) {
                    $descr = 'xxxxx';
                } else {
                    $descr = '';
                }

                if ($increment_hmonths > 0) {
                    $counter = substr($my_date, 0, 6);
                } else {
                    $counter = substr($my_date, 0, 4);
                }
                if ($calc_greg) {
                    $my_gdate = self::hijriToGreg($my_date);
                } else {
                    $my_gdate = '';
                }
                $arr_hij_period[$my_date] = [
                    'hdate' => $my_date,
                    'greg' => $my_gdate,
                    'counter' => $counter,
                    'descr' => $descr,
                ];

                $my_date = self::addHijriPeriodToHijriDate(
                    $my_date,
                    $increment_hmonths,
                    $increment_hyears
                );
            }
        }

        return $arr_hij_period;
    }

    public static function gregToTimestamp($gdate)
    {
        $arr_dat = explode(' ', $gdate);
        $arr_day = explode('-', $arr_dat[0]);
        $arr_hour = explode(':', $arr_dat[1]);
        if (!$arr_hour[0]) {
            $arr_hour[0] = 0;
        }
        if (!$arr_hour[1]) {
            $arr_hour[1] = 0;
        }
        if (!$arr_hour[2]) {
            $arr_hour[2] = 0;
        }

        if (!$arr_day[0]) throw new AfwRuntimeException("bad greg date when calling gregToTimestamp($gdate)");
        $tmstmp = mktime(
            $arr_hour[0],
            $arr_hour[1],
            $arr_hour[2],
            $arr_day[1],
            $arr_day[2],
            $arr_day[0]
        );

        return $tmstmp;
    }

    public static function weekDayOfHijriDate($hdate = '')
    {
        if (!$hdate) {
            $hdate = self::currentHijriDate();
        }

        $gdate = self::hijriToGreg($hdate);
        return self::weekDayOf($gdate);
    }

    public static function weekDayOf($gdate = '')
    {
        if (!$gdate) {
            $gdate = date('Y-m-d');
        }
        $tms_0 = self::gregToTimestamp($gdate);
        return date('w', $tms_0);
    }



    public static function hijri_current_long_date($Separator = ' ')
    {
        return self::currentHijriDate($mode = 'hdate_long', $Separator);
    }

    public static function currentHijriDate($mode = 'hdate', $Separator = ' ')
    {
        return self::to_hijri(date('Ymd'), $mode, $Separator);
    }


    public static function diff_date($madate2, $madate1, $round = true)
    {
        if (strpos($madate2, '-') === false) {
            $madate2 = self::add_dashes($madate2);
        }

        if (strpos($madate1, '-') === false) {
            $madate1 = self::add_dashes($madate1);
        }


        $stmp2 =   self::dateToTimestamp($madate2);
        $stmp1 =   self::dateToTimestamp($madate1);


        $result_diff = ($stmp2 - $stmp1) / (24 * 3600);
        if ($round) $result_diff = round($result_diff);

        return $result_diff;
    }

    /*
        public static function weekDayOfHijriDate($hdate="")
        {
                $MyDays = array("الأحد", "الأثنين", "الثلاثاء", "الإربعاء", "الخميس", 
                                "الجمعة", "السبت");
                                
                if(!$hdate) $hdate = self::currentHijriDate();
                $gdate = self::AfwDateHelper::hijriToGreg($hdate);
                $tms_0 = from_mysql_to_timestamp($gdate);
                $wday = date('w',$tms_0);
                
                return array($wday+1, $MyDays[$wday]);
        }
        */
    /*
    public static function add_x_days_to_hijridate($hdate, $xdays)
    {
        if (!$hdate) {
            $hdate = self::currentHijriDate();
        }

        if (strpos($hdate, '/') === false) {
            $hdate_cs = self::add_slashes($hdate);
        }

        $hd_arr = explode('/', $hdate_cs);
        //echo "<br>hd_arr = ".var_export($hd_arr,true);
        $hijri_year = intval($hd_arr[0]);
        $hijri_month = intval($hd_arr[1]);
        $hijri_day = intval($hd_arr[2]);

        $hijri_day_new = $hijri_day + $xdays;
        if ($hijri_day_new <= 29 and $hijri_day_new > 0) {
            $hd_arr[2] = str_pad($hijri_day_new, 2, '0', STR_PAD_LEFT);
            return $hd_arr[0] . $hd_arr[1] . $hd_arr[2];
        }

        $gdate = self::hijriToGreg($hdate);
        $gdate = add_x_days_to_mysqldate($xdays, $gdate);

        return self::to_hijri($gdate);
    }*/

    public static function genereHijriPeriod(
        $from_date,
        $to_date,
        $we_arr = [6, 7],
        $system = 'GREG',
        $increment_days = 1,
        $increment_months = 0,
        $increment_years = 0,
        $calc_greg = true,
        $include_to_date = true,
        $throwError = true
    ) {
        if (!$from_date or !$to_date) {
            if ($throwError) throw new AfwRuntimeException("genereHijriPeriod(from_date=$from_date,to_date=$to_date,,system=$system,increment_days=$increment_days, increment_months=$increment_months, increment_years=$increment_years, calc_greg=$calc_greg, include_to_date=$include_to_date) can't be performed, from and to dates are mandatory!!");
            else return "error 1";
        }

        if ($system == 'HIJRI') {
            $old_from_date = $from_date;
            $old_to_date = $to_date;
            $from_date = self::hijriToGreg($from_date);
            $to_date = self::hijriToGreg($to_date);
            //die("from_date ($old_from_date) => $from_date, to_date($old_to_date) => $to_date");
        }

        $my_date = $from_date;

        $arr_hij_period = [];

        while (
            $include_to_date and $my_date <= $to_date or
            !$include_to_date and $my_date < $to_date
        ) {
            $hdate = self::to_hijri($my_date);
            if (strlen($hdate) != 8) {
                if ($throwError) throw new AfwRuntimeException("error : $hdate = to_hijri(my_date='$my_date')");
                else return "error 2";
            }

            $wday = self::weekDayOf($my_date) + 1;
            //if($wday==0) $wday = 7;
            // if(($wday==6) or ($wday==7))
            if (in_array($wday, $we_arr)) {
                $free = 'Y';
                $descr = 'نهاية الاسبوع';
            } else {
                $free = 'N';
                $descr = '';
            }

            if ($increment_days > 0) {
                $counter = substr($hdate, 4, 4);
            } elseif ($increment_months > 0) {
                $counter = substr($hdate, 0, 6);
            } elseif ($increment_years > 0) {
                $counter = substr($hdate, 0, 4);
            }

            $arr_hij_period[$my_date] = [
                'hdate' => $hdate,
                'greg' => $my_date,
                'counter' => $counter,
                'wday' => $wday,
                'free' => $free,
                'descr' => $descr,
            ];
            //die("arr_hij_period = ".var_export($arr_hij_period,true));
            $my_date = self::addPeriodToGregDate(
                $increment_days,
                $increment_months,
                $increment_years,
                $my_date
            );
        }

        // die("arr_hij_period = ".var_export($arr_hij_period,true));
        return $arr_hij_period;
    }

    public static function gregToHijri($gdate, $mode = 'hdate', $ifSeemsHijriKeepAsIs = false, $throwError = true)
    {
        /*
                list($year,$month,$day) = self::splitGregDate($gdate);
                return self::julianToHijri(self::gregorianToJulian(intval($year), intval($month), intval($day)));
                */
        list($gdate, $gtime) = explode(' ', $gdate);
        if ($gdate == "0000-00-00") {
            if ($mode == 'hdate') return "00000000";
            if ($mode == 'hdate-dashed') return "0000-00-00";
            return "$gdate ? how in mode $mode";
        }

        return self::to_hijri($gdate, $mode, ' ', true, $ifSeemsHijriKeepAsIs, $throwError);
    }

    public static function repareGorbojHijriDate($hdate, $without_dashes = true)
    {
        $hdate = trim($hdate);
        $hdate = str_replace('/', '-', $hdate);
        $hdate_arr = explode('-', $hdate);

        $is_greg = false;

        $hdate_arr[0] = intval(trim($hdate_arr[0]));
        $hdate_arr[1] = intval(trim($hdate_arr[1]));
        $hdate_arr[2] = intval(trim($hdate_arr[2]));

        // swap day and year if needed
        if ($hdate_arr[0] <= 31 and $hdate_arr[2] > 1000) {
            $tmps = $hdate_arr[2];
            $hdate_arr[2] = $hdate_arr[0];
            $hdate_arr[0] = $tmps;
        }

        // swap day and month if needed
        if ($hdate_arr[1] > 12 and $hdate_arr[2] <= 12) {
            $tmps = $hdate_arr[2];
            $hdate_arr[2] = $hdate_arr[1];
            $hdate_arr[1] = $tmps;
        }

        // 0 leftpad if needed
        $hdate_arr[1] = str_pad($hdate_arr[1], 2, '0', STR_PAD_LEFT);
        $hdate_arr[2] = str_pad($hdate_arr[2], 2, '0', STR_PAD_LEFT);

        $hdate = implode('-', $hdate_arr);

        if ($hdate_arr[0] > 1800) {
            $is_greg = true;
        }

        if ($is_greg) {
            list($hijri_year, $mm, $dd) = self::gregToHijri($hdate, 'hlist');

            $hdate = "$hijri_year-$mm-$dd";
        }

        if ($without_dashes) {
            $hdate = self::repareHijriDate($hdate);
        }

        return $hdate;
    }


    public static function repareGorbojGregDate($gdate)
    {
        $gdate = trim($gdate);
        $gdate = str_replace('/', '-', $gdate);
        $gdate_arr = explode('-', $gdate);

        $is_hijri = false;

        $gdate_arr[0] = intval(trim($gdate_arr[0]));
        $gdate_arr[1] = intval(trim($gdate_arr[1]));
        $gdate_arr[2] = intval(trim($gdate_arr[2]));

        // swap day and year if needed
        if ($gdate_arr[0] <= 31 and $gdate_arr[2] > 1000) {
            $tmps = $gdate_arr[2];
            $gdate_arr[2] = $gdate_arr[0];
            $gdate_arr[0] = $tmps;
        }

        // swap day and month if needed
        if ($gdate_arr[1] > 12 and $gdate_arr[2] <= 12) {
            $tmps = $gdate_arr[2];
            $gdate_arr[2] = $gdate_arr[1];
            $gdate_arr[1] = $tmps;
        }

        // 0 leftpad if needed
        $gdate_arr[1] = str_pad($gdate_arr[1], 2, '0', STR_PAD_LEFT);
        $gdate_arr[2] = str_pad($gdate_arr[2], 2, '0', STR_PAD_LEFT);

        $gdate = implode('-', $gdate_arr);

        if ($gdate_arr[0] < 1800) {
            $is_hijri = true;
        }

        if ($is_hijri) {
            $gdate = self::hijriToGreg($gdate);
        }

        return $gdate;
    }

    public static function repareHijriDate($hdate)
    {
        return implode('', explode('-', $hdate));
    }

    public static function hijriToGreg($hdate, $throwError = true)
    {
        /*
                list($year,$month,$day) = self::splitHijriDate(self::repareHijriDate($hdate));
                //die("list($year,$month,$day) = self::splitHijriDate(self::repareHijriDate($hdate))");
                return self::julianToGregorian(self::hijriToJulian(intval($year), intval($month), intval($day)));
                */

        return self::hijri_to_greg($hdate, $throwError);
    }

    private static function gregdate_of_first_hijri_day(
        $hijri_year,
        $hijri_month,
        $throwError = true
    ) {
        global $hgreg_matrix;
        if (!$hgreg_matrix) {
            $hgreg_matrix = [];
        }
        if ($hgreg_matrix[$hijri_year . $hijri_month]) {
            return $hgreg_matrix[$hijri_year . $hijri_month];
        }

        $hijri_month_full = ($hijri_month < 10) ? "0" . $hijri_month : $hijri_month;

        $gdfirst = self::hijri_to_greg_from_files($hijri_year . $hijri_month_full . "01");
        if ($gdfirst) {
            $hgreg_matrix[$hijri_year . $hijri_month] = $gdfirst;
        } else {
            //if(count($hgreg_matrix)>0) die("gregdate_of_first_hijri_day($hijri_year, $hijri_month) : ".var_export($hgreg_matrix,true));
            $server_db_prefix = AfwSession::config('db_prefix', "default_db_");
            $sql_greg = " select greg_date
                            from $server_db_prefix" . "cmn.hijra_date_base 
                            where hijri_year = $hijri_year
                                    and hijri_month = $hijri_month";
            //echo "<br>sql_greg = $sql_greg";

            $greg_date = AfwDatabase::db_recup_value($sql_greg);
            if (!$greg_date) {
                if ($throwError) throw new AfwRuntimeException("Error : no greg date in cmn.hijra_date_base for hijri_year = $hijri_year and hijri_month = $hijri_month : $sql_greg");
                else return "";
            }
            $hgreg_matrix[$hijri_year . $hijri_month] = self::add_dashes($greg_date);
        }

        return $hgreg_matrix[$hijri_year . $hijri_month];
    }


    public static function long_hijri_date($hijri_year, $mm, $dd, $TheDay, $WeekDayOn = 1, $YearOn = 1, $MonthNameOn = 1, $Separator = " ")
    {

        $MyMonths = array(
            "محرم",
            "صفر",
            "ربيع الأول",
            "ربيع الآخر",
            "جمادى الأولى",
            "جمادى الآخرة",
            "رجب",
            "شعبان",
            "رمضان",
            "شوّال",
            "ذو القعدة",
            "ذو الحجة"
        );

        $MyDays = array(
            "الأحد",
            "الأثنين",
            "الثلاثاء",
            "الإربعاء",
            "الخميس",
            "الجمعة",
            "السبت"
        );

        $MyDateFinal = $dd . $Separator;
        if ($MonthNameOn)
            $MyDateFinal .= $MyMonths[$mm - 1];
        else
            $MyDateFinal .= $mm;

        if ($WeekDayOn) $MyDateFinal = $MyDays[$TheDay["wday"]] . $Separator . $MyDateFinal;
        if ($YearOn) $MyDateFinal .= $Separator . $hijri_year;

        return $MyDateFinal;
    }

    public static function hdateDecompose($hdate)
    {
        $hdate_YYYY = substr($hdate, 0, 4);
        $hdate_MM = substr($hdate, 4, 2);
        $hdate_DD = substr($hdate, 6, 2);

        return [$hdate_YYYY, $hdate_MM, $hdate_DD];
    }

    public static function hdateWithSeparator($hdate, $sep = "-")
    {
        $hdate_YYYY = substr($hdate, 0, 4);
        $hdate_MM = substr($hdate, 4, 2);
        $hdate_DD = substr($hdate, 6, 2);

        return $hdate_YYYY . $sep . $hdate_MM . $sep . $hdate_DD;
    }

    public static function to_hijri(
        $gdate,
        $mode = 'hdate',
        $separator = ' ',
        $emptyIsCurrent = true,
        $ifSeemsHijriKeepAsIs = false,
        $throwError = true
    ) {
        /******* preparations ************/

        // remove time
        list($gdate,) = explode(" ", $gdate);

        if ($emptyIsCurrent and !$gdate) {
            $gdate = date('Y-m-d');
        }

        // without dashes to gdate
        $wd_gdate = self::remove_dashes($gdate);
        if (strlen($wd_gdate) != 8) {
            if ($throwError) throw new AfwRuntimeException(
                "to_hijri : gdate($gdate) after self::remove_dashes = $wd_gdate, not ok"
            );
            else return "error 3";
        }

        if (($wd_gdate <= '19700101') and (!$ifSeemsHijriKeepAsIs)) {
            if ($throwError) throw new AfwRuntimeException(
                "to_hijri : gdate($gdate) after self::remove_dashes = $wd_gdate is not greg known greg date"
            );
            else return "error 4";
        }

        // readd dashes to gdate
        $gdate = self::add_dashes($wd_gdate);
        if (strlen($gdate) != 10) {
            if ($throwError) throw new AfwRuntimeException(
                "to_hijri : gdate after re-add_dashes($wd_gdate) = $gdate, not ok"
            );
            else return "error 5";
        }

        /******* end of preparations ************/

        if ($mode == "hdate-dashed") {
            $result = self::gregToHijri(
                $gdate,
                'hdate',
                $separator,
                $emptyIsCurrent,
                $ifSeemsHijriKeepAsIs
            );
            return self::hdateWithSeparator($result, $separator);
        }

        if ($mode == "hlist") {
            $result = self::gregToHijri(
                $gdate,
                'hdate',
                $separator,
                $emptyIsCurrent,
                $ifSeemsHijriKeepAsIs
            );
            return self::hdateDecompose($result);
        }

        if ($mode == 'hdate_long') {
            $DF = explode('-', $gdate);
            $df_yyyy = $DF[0];
            $df_mm = $DF[1];
            $df_dd = $DF[2];

            $TheDay = getdate(mktime(0, 0, 0, $df_mm, $df_dd, $df_yyyy));
            // if($gdate=="2020-04-04") die("getdate(mktime(0,0,0,$df_mm,$df_dd,$df_yyyy)) = ".var_export($TheDay,true));
            list($hijri_year, $mm, $dd) = self::to_hijri($gdate, 'hlist');
            return self::long_hijri_date(
                $hijri_year,
                $mm,
                $dd,
                $TheDay,
                1,
                1,
                1,
                $separator
            );
        }

        if (($wd_gdate <= '15100101') and $ifSeemsHijriKeepAsIs) {
            return $wd_gdate;
        }

        $result = AfwSession::getVar("hijri-of-$gdate");
        if ($result) return $result;

        // try to use cache greg_to_hijri files
        list($greg_year,) = explode("-", $gdate);
        $hg_cache_file = dirname(__FILE__) . "/../../../external/chsys/dates/greg_$greg_year" . "_to_hijri.php";
        $greg_to_hijri_arr = include($hg_cache_file);

        if ($greg_to_hijri_arr) {
            $result = $greg_to_hijri_arr[$gdate];
            if ($result) {
                AfwSession::setVar("hijri-of-$gdate", $result);
                return $result;
            }
        }
        //else die("please check $hg_cache_file");
        $server_db_prefix = AfwSession::config('db_prefix', "default_db_");
        $sql_hij = "select hijri_year as HY,
                        hijri_month as HM,
                        greg_date as GD
                        from $server_db_prefix" . "cmn.hijra_date_base
                where greg_date = (select max(greg_date) from $server_db_prefix" . "cmn.hijra_date_base where greg_date <= '$wd_gdate')";

        $row_hijri = AfwDatabase::db_recup_row($sql_hij);

        $hijri_year = $row_hijri['HY'];
        $hijri_month = $row_hijri['HM'];
        $greg_date = $row_hijri['GD'];
        // die("row_hijri for $wd_gdate = ".var_export($row_hijri,true));
        if (strpos($greg_date, '-') === false) {
            $greg_date = self::add_dashes($greg_date);
        }

        //die("$sql_hij => $greg_date");

        $mm = str_pad($hijri_month, 2, '0', STR_PAD_LEFT);

        $hijri_day = self::diff_date($gdate, $greg_date) + 1;

        $dd = str_pad($hijri_day, 2, '0', STR_PAD_LEFT);

        //if(intval($dd)>30) die("to_hijri : $hijri_day = diff_date($gdate,$greg_date) + 1 --> padded $dd ");
        AfwSession::setVar("hijri-of-$gdate", "$hijri_year" . $mm . "$dd");

        $return = AfwSession::getVar("hijri-of-$gdate");
        if (($mode == 'hdate') and (strlen($return) != 8)) {
            if ($throwError) throw new AfwRuntimeException("Error converting $wd_gdate from DB => row_hijri=" . var_export($row_hijri, true) . " => hijri_day = $hijri_day = diff_date($gdate,$greg_date) + 1 => return=$return");
            else return "error 6";
        }

        if ($return) {
            return $return;
        } else {
            return '?????';
        }
    }

    private static function hijri_to_greg_from_files($original_hdate, $hijri_year = "")
    {
        // try to use cache hijri_to_greg files
        if (!$hijri_year) $hijri_year = substr($original_hdate, 0, 4);
        $hijri_to_greg_file = dirname(__FILE__) . "/../../../external/chsys/dates/hijri_" . $hijri_year . "_to_greg.php";
        $hijri_to_greg_arr = include($hijri_to_greg_file);
        /*
        if(($original_hdate=="14350101") and (!$hijri_to_greg_arr[$original_hdate]))
        {
            die("$original_hdate not found in hijri_to_greg_file=$hijri_to_greg_file in hijri_to_greg_arr=".var_export($hijri_to_greg_arr,true));
        }*/

        $hdate = self::remove_dashes($original_hdate);
        if (!$hijri_to_greg_arr[$hdate]) {
            $hdate_dashed = self::add_dashes($hdate);
            list($yyyy, $mm, $dd) = explode("-", $hdate_dashed);
            if ($dd == "30") // ex 1436-03-30 doesn't exists max is 1436-03-29 (month 1436-03 is 29 days only)  we consider 1436-04-01
            {
                $dd = "01";
                $mm = intval($mm) + 1;
                if ($mm > 12) {
                    $mm = 1;
                    $yyyy += 1;
                }

                if ($mm < 10) $mm = "0" . $mm;

                $hdate = $yyyy . $mm . $dd;
            }
        }
        if (!$hijri_to_greg_arr[$hdate]) {
            AfwSession::hzmLog("failed to find hijri_to_greg[$hdate] ($original_hdate) in file $hijri_to_greg_file ", "fail"); // ."hijri_to_greg = ".var_export($hijri_to_greg_arr,true)
            if ($hijri_year > 1430) throw new RuntimeException("kifech ma convertech hijri_to_greg[$hdate] ($original_hdate) chouf $hdate in file $hijri_to_greg_file");
        }
        return $hijri_to_greg_arr[$hdate];
    }



    private static function hijri_to_greg_from_estimation($hdate)
    {
        if (strpos($hdate, '-') !== false) {
            $hdate = self::remove_dashes($hdate);
        }

        if (strpos($hdate, '/') === false) {
            $hdate = self::add_slashes($hdate);
        }

        list($yyyy, $mm, $dd) = explode('/', $hdate);

        $hdate_formatted = $yyyy . $mm . $dd;

        $yyyy_diff = $yyyy - 1380;
        $mm_diff = $mm - 1;
        $dd_diff = $dd - 1;

        $estimated_days_diff_from_1380_01_01 = round($yyyy_diff * 354 + $mm_diff * 29.5 + $dd_diff);

        // 01-01-1380 = الاحد 26 يونيو 1960 from https://www.ummulqura.org.sa/

        $gdate = self::shiftGregDate("1960-06-26", $estimated_days_diff_from_1380_01_01);

        return $gdate;
    }

    private static function hijri_to_greg($hdate, $throwError = true)
    {
        $original_hdate = $hdate;
        if (strpos($hdate, '-') !== false) {
            $hdate = self::remove_dashes($hdate);
        }

        if (strpos($hdate, '/') === false) {
            $hdate = self::add_slashes($hdate);
        }

        $hd_arr = explode('/', $hdate);

        $hdate = $hd_arr[0] . $hd_arr[1] . $hd_arr[2];

        $result = AfwSession::getVar("greg-of-$hdate");
        if ($result) return $result;


        $hijri_year = intval($hd_arr[0]);
        $hijri_month = intval($hd_arr[1]);
        $hijri_day = intval($hd_arr[2]);
        if ($original_hdate >= self::$hijri_date_limit) {
            $result = self::hijri_to_greg_from_files($original_hdate, $hijri_year);
            $log_ma_convertech = "hijri_to_greg_from_files($original_hdate, $hijri_year)";
        } else {
            $result = self::hijri_to_greg_from_estimation($original_hdate);
            $log_ma_convertech = "hijri_to_greg_from_estimation($original_hdate)";
        }

        if ($result) {
            AfwSession::setVar("greg-of-$hdate", $result);
            return $result;
        } else {
            throw new RuntimeException("kifech ma convertech ? $log_ma_convertech");
        }

        $first_gregdate = self::gregdate_of_first_hijri_day(
            $hijri_year,
            $hijri_month,
            $throwError
        );
        if ($first_gregdate) {
            $greg_date = self::addXDaysToGregDate($hijri_day - 1, $first_gregdate);

            if ($greg_date) {
                AfwSession::setVar("greg-of-$hdate", $greg_date);
                return $greg_date;
            }
        } else $greg_date = $hdate; // if can not let it like it was


        return $greg_date;
    }

    public static function shiftHijriDate($hdate = '', $offset = 1)
    {
        if (!$hdate) {
            $hdate = self::currentHijriDate();
        }
        $gdate = self::hijriToGreg($hdate);
        $gdate = self::shiftGregDate($gdate, $offset);

        return self::gregToHijri($gdate);
    }

    public static function shiftPeriodHijriDate(
        $hdate = '',
        $days,
        $months,
        $years
    ) {
        if (!$hdate) {
            $hdate = self::currentHijriDate();
        }
        $gdate = self::hijriToGreg($hdate);
        $gdate = self::shiftPeriodGregDate($gdate, $days, $months, $years);

        return self::gregToHijri($gdate);
    }

    /**
     *  @param string $gdate if omitted we take current greg date
     *  @param integer $offset number of days offset to shift
     */

    public static function shiftGregDate($gdate, $offset)
    {
        return self::shiftPeriodGregDate($gdate, $offset, 0, 0);
    }

    public static function shiftPeriodGregDate($gdate, $days, $months, $years)
    {
        if (!$gdate) {
            $gdate = date('Y-m-d');
        }
        $gdate_tab = explode('-', $gdate);
        $gdate_shifted = date(
            'Y-m-d',
            mktime(
                0,
                0,
                0,
                $gdate_tab[1] + $months,
                $gdate_tab[2] + $days,
                $gdate_tab[0] + $years
            )
        );

        return $gdate_shifted;
    }

    public static function hijriDateTimeDiff(
        $hdate2 = '',
        $time2 = '',
        $hdate1 = '',
        $time1 = '',
        $round = true
    ) {
        if (!$hdate2) {
            $gdate2 = date('Y-m-d');
        } else {
            $gdate2 = self::hijriToGreg($hdate2);
        }

        if (!$hdate1) {
            $gdate1 = date('Y-m-d');
        } else {
            $gdate1 = self::hijriToGreg($hdate1);
        }

        if (!$time2) {
            $time2 = date('H:i:s');
        }
        if (!$time1) {
            $time1 = date('H:i:s');
        }

        $return = self::gregDateDiff(
            $gdate2 . ' ' . $time2,
            $gdate1 . ' ' . $time1,
            $round
        );

        // die("$return = [$hdate2 => $gdate2] - [$hdate1 => $gdate1]");

        return $return;
    }

    public static function hijriDateDiff($hdate2, $hdate1, $round = true)
    {
        $gdate1 = self::hijriToGreg($hdate1);
        $gdate2 = self::hijriToGreg($hdate2);

        $return = self::gregDateDiff($gdate2, $gdate1, $round);

        // die("$return = [$hdate2 => $gdate2] - [$hdate1 => $gdate1]");

        return $return;
    }

    public static function getHijriDateTimeFromGregDateTime(
        $gdatetime,
        $seconds = true,
        $throwError = true
    ) {
        list($gdate, $gtime) = explode(' ', $gdatetime);

        $hdate = self::to_hijri($gdate);
        if (strlen($hdate) != 8) {
            if ($throwError) throw new AfwRuntimeException(
                "list($gdate, $gtime) = explode(' ',$gdatetime) => $hdate = to_hijri($gdate)"
            );
            else return "error 7";
        }
        if (!$seconds) {
            $gtime = substr($gtime, 0, 5);
        }

        return [$hdate, $gtime];
    }

    public static function timeDiffInSeconds($gdate2, $gdate1)
    {
        $stmp2 = self::gregToTimestamp($gdate2);
        $stmp1 = self::gregToTimestamp($gdate1);

        return ($stmp2 - $stmp1);
    }

    public static function gregDateDiff($gdate2, $gdate1, $round = true)
    {
        $result_diff = self::timeDiffInSeconds($gdate2, $gdate1);
        $result_diff / (24 * 3600);
        if ($round) {
            $result_diff = round($result_diff);
        }

        return $result_diff;
    }

    /*****************     date and time functions    *************************/

    public static function addDatetimeToGregDatetime(
        $gdate_time = '',
        $years = 0,
        $months = 0,
        $days = 0,
        $hours = 0,
        $minutes = 0,
        $seconds = 0
    ) {
        if (!$gdate_time) {
            $gdate_time = date('Y-m-d H:i:s');
        }

        $arr_dat = explode(' ', $gdate_time);
        $arr_day = explode('-', $arr_dat[0]);
        $arr_hour = explode(':', $arr_dat[1]);

        $tmstmp = mktime(
            $arr_hour[0] + $hours,
            $arr_hour[1] + $minutes,
            $arr_hour[2] + $seconds,
            $arr_day[1] + $months,
            $arr_day[2] + $days,
            $arr_day[0] + $years
        );

        return date('Y-m-d H:i:s', $tmstmp);
    }

    /*****************    time functions    *************************/

    public static function getSplittedTime($time_to_add)
    {
        if (is_array($time_to_add)) {
            return $time_to_add;
        }

        if (is_numeric($time_to_add)) {
            $hh_to_add = floor($time_to_add);
            $ii_to_add = floor(($time_to_add - $hh_to_add) * 60);
            $ss_to_add = round(
                (($time_to_add - $hh_to_add) * 60 - $ii_to_add) * 60
            );
        } else {
            list($hh_to_add, $ii_to_add, $ss_to_add) = explode(
                ':',
                $time_to_add
            );
            $hh_to_add = intval($hh_to_add);
            $ii_to_add = intval($ii_to_add);
            $ss_to_add = intval($ss_to_add);
        }

        return [$hh_to_add, $ii_to_add, $ss_to_add];
    }

    /**
     *
     * add time to time
     *
     */

    public static function addTimeToDayTime(
        $day,
        $time,
        $time_to_add,
        $seconds = false,
        $sign = 1
    ) {
        list($hh_to_add, $ii_to_add, $ss_to_add) = self::getSplittedTime(
            $time_to_add
        );

        if (strlen($time) == 5) {
            $time .= ':00';
        }
        $to_day = date('Y-m-d');
        $date_time_day = $to_day . ' ' . $time;

        $new_date_time = self::addDatetimeToGregDatetime(
            $date_time_day,
            $years = 0,
            $months = 0,
            $days = 0,
            $sign * $hh_to_add,
            $sign * $ii_to_add,
            $sign * $ss_to_add
        );

        list($new_date, $new_time) = explode(' ', $new_date_time);

        if (!$seconds) {
            $new_time = substr($new_time, 0, 5);
        }

        $new_day = $day + self::diff_date($new_date, $to_day);

        return [$new_day, $new_time];
    }

    public static function getPeriodDefinitionByName($period_name, $margin = 0)
    {
        if ($period_name == 'now') {
            return [['today', 0, 0], ['today', 30, 0]];
        }
        if ($period_name == 'this_week') {
            return [['today', 0, 0], ['today', 6 + $margin, '-w']];
        }
        if ($period_name == 'nextweek') {
            return [['today', 7, '-w'], ['today', 13 + $margin, '-w']];
        }
        if ($period_name == 'after2w') {
            return [['today', 14, '-w'], ['today', 20 + $margin, '-w']];
        }
        if ($period_name == 'nextmonth') {
            return [['today', 21, '-w'], ['today', 81 + $margin, '-w']];
        }

        throw new AfwRuntimeException(
            "AfwDateHelper::getPeriodDefinitionByName:  period name : '$period_name' unknown"
        );
    }

    public static function dateDefinitionToGDate($date_definition)
    {
        list(
            $date_definition_start,
            $date_definition_offset,
            $date_definition_weekday_offset,
        ) = $date_definition;
        if ($date_definition_start == 'today') {
            $gdate_start = date('Y-m-d');
        } else {
            throw new AfwRuntimeException(
                "AfwDateHelper::dateDefinitionToGDate:  date_definition_start : '$date_definition_start' unknown"
            );
        }

        $w = self::weekDayOf($gdate_start);

        $offset = $date_definition_offset;
        if ($date_definition_weekday_offset === '+w') {
            $offset += $w;
        } elseif ($date_definition_weekday_offset === '-w') {
            $offset -= $w;
        } elseif ($date_definition_weekday_offset) {
            throw new AfwRuntimeException(
                "AfwDateHelper::dateDefinitionToGDate:  date_definition_weekday_offset : '$date_definition_weekday_offset' unknown"
            );
        }

        return self::shiftGregDate($gdate_start, $offset);
    }

    public static function getTimingInfosByDefinition($period_definition)
    {
        list($gdate_from_definition, $gdate_to_definition) = $period_definition;

        $gdate_from = self::dateDefinitionToGDate($gdate_from_definition);
        $gdate_to = self::dateDefinitionToGDate($gdate_to_definition);

        $hdate_from = self::gregToHijri($gdate_from);
        $hdate_to = self::gregToHijri($gdate_to);

        return [
            'gfrom' => $gdate_from,
            'gto' => $gdate_to,
            'hfrom' => $hdate_from,
            'hto' => $hdate_to,
        ];
    }

    public static function getTimingInfosByName($period_name, $margin = 0)
    {
        return self::getTimingInfosByDefinition(
            self::getPeriodDefinitionByName($period_name, $margin)
        );
    }

    public static function add_slashes($madate)
    {
        $madate_YYYY = substr($madate, 0, 4);
        $madate_MM = substr($madate, 4, 2);
        $madate_DD = substr($madate, 6, 2);

        return "$madate_YYYY/$madate_MM/$madate_DD";
    }

    public static function add_dashes($madate)
    {
        $madate_YYYY = substr($madate, 0, 4);
        $madate_MM = substr($madate, 4, 2);
        $madate_DD = substr($madate, 6, 2);

        return "$madate_YYYY-$madate_MM-$madate_DD";
    }

    public static function remove_dashes($gdate)
    {
        $arr_gdate = explode('-', $gdate);
        $madate_YYYY = $arr_gdate[0];
        $madate_MM = $arr_gdate[1];
        $madate_DD = $arr_gdate[2];

        return $madate_YYYY . $madate_MM . $madate_DD;
    }

    public static function gdateIsWeekend($gdate, $we_arr = [6, 7])
    {
        $day_of_week = self::weekDayOf($gdate) + 1;

        return in_array($day_of_week, $we_arr);
    }

    public static function calculateTimeInstersection($start_date_time, $end_date_time, $time_frame = ['08:00:00', '14:00:00'], $we_arr = [6, 7], $leave_gdates = array())
    {
        list($start_date, $start_time) = explode(" ", $start_date_time);
        list($end_date, $end_time) = explode(" ", $end_date_time);
        $curr_date = $start_date;
        $total_time_min = 0;
        while ($curr_date <= $end_date) {
            if (in_array($curr_date, $leave_gdates) or self::gdateIsWeekend($curr_date, $we_arr)) {
                // we ignore this day as it is we or leave
            } else {
                if ($curr_date == $start_date) {
                    $time_start1 = $start_time;
                    $time_end1 = "23:59:59";
                } elseif ($curr_date == $end_date) {
                    $time_start1 = "00:00:00";
                    $time_end1 = $end_time;
                } else {
                    $time_start1 = "00:00:00";
                    $time_end1 = "23:59:59";
                }
                list($time_start2, $time_end2) = $time_frame;

                $total_time_min += round(self::timeIntersectionInSeconds($time_start1, $time_end1, $time_start2, $time_end2) / 60);
            }
            $curr_date = self::shiftGregDate($curr_date, 1);
        }


        return $total_time_min;
    }

    public static function timeIntersectionInSeconds($time_start1, $time_end1, $time_start2, $time_end2)
    {
        $time_start = ($time_start1 > $time_start2) ? $time_start1 : $time_start2;
        $time_end = ($time_end1 < $time_end2) ? $time_end1 : $time_end2;

        $today = date("Y-m-d");
        $result = self::timeDiffInSeconds($today . " " . $time_start, $today . " " . $time_end);

        return $result;
    }

    public static function getPrayerTimeList()
    {
        $time_arr = array();

        $time_arr["03:01"] = "بعد صلاة الفجر";
        $time_arr["04:01"] = "بعد صلاة الفجر حصة 2";
        $time_arr["12:01"] = "بعد صلاة الظهر";
        $time_arr["13:01"] = "بعد صلاة الظهر حصة 2";
        $time_arr["15:01"] = "بعد صلاة العصر";
        $time_arr["16:01"] = "بعد صلاة العصر حصة 2";
        $time_arr["18:01"] = "بعد صلاة المغرب";
        $time_arr["19:01"] = "بعد صلاة المغرب حصة 2";
        $time_arr["20:01"] = "بعد صلاة العشاء";
        $time_arr["21:01"] = "بعد صلاة العشاء حصة 2";


        return $time_arr;
    }

    public static function getAfterPrayerTimeList()
    {
        $time_arr = array();

        $time_arr["04:01"] = "بعد صلاة الفجر بساعة";
        $time_arr["05:01"] = "بعد صلاة الفجر حصة 2 بساعة";
        $time_arr["13:01"] = "بعد صلاة الظهر بساعة";
        $time_arr["14:01"] = "بعد صلاة الظهر حصة 2 بساعة";
        $time_arr["16:01"] = "بعد صلاة العصر بساعة";
        $time_arr["17:01"] = "بعد صلاة العصر حصة 2 بساعة";
        $time_arr["19:01"] = "بعد صلاة المغرب بساعة";
        $time_arr["20:01"] = "بعد صلاة المغرب حصة 2 بساعة";
        $time_arr["21:01"] = "بعد صلاة العشاء بساعة";
        $time_arr["22:01"] = "بعد صلاة العشاء حصة 2 بساعة";


        return $time_arr;
    }

    public static function getTimeInterval($medium = 8, $interval = 1, $increment = 15)
    {
        return self::getTimeArray($start = $medium - $interval, $increment, $end = $medium + $interval);
    }

    public static function formatTimeHHNN($hh, $mm)
    {
        $time = "";
        if ($hh < 10) $time .= "0";
        $time .= $hh . ":";
        if ($mm < 10) $time .= "0";
        $time .= $mm;

        return $time;
    }

    public static function getTimeArray($start = 7, $increment = 15, $end = 21)
    {
        $hh = $start;
        $mm = 0;

        $time_arr = array();

        while ($hh < $end) {
            $time = self::formatTimeHHNN($hh, $mm);
            $time_arr[$time] = $time;

            $mm += $increment;

            if ($mm >= 60) {
                $mm = $mm - 60;
                $hh++;
            }
        }

        return $time_arr;
    }


    public static function displayTime($value, $structure, $decode_format, $object = null)
    {


        if ($decode_format == 'CLASS') {
            $helpClass = $structure["ANSWER_CLASS"];
            $helpMethod = $structure["ANSWER_METHOD"];

            $answer_list = $helpClass::$helpMethod();
            $hr = $value;
            return $answer_list[$hr];
        } elseif (($decode_format == 'OBJECT') and $object) {
            $helpMethod = $structure["ANSWER_METHOD"];
            $answer_list = $object->$helpMethod();
            $hr = $value;
            return $answer_list[$hr];
        } elseif (($decode_format == 'HEURE') or  ($decode_format == 'TIME-WITHOUT-SECONDS')) {
            $hr = $value;
            $hr = explode(':', $hr);

            $return = $hr[0] . ':' . $hr[1];
        } elseif ($decode_format == 'TIME') {
            $hr = $value;
            $hr = explode(':', $hr);

            $return = $hr[0] . ':' . $hr[1] . ':' . $hr[2];
        } elseif ($decode_format == 'ARABIC-TIME') {
            $hr = $value;
            $hr = explode(':', $hr);

            $return =
                'س' . $hr[0] . ' و' . $hr[1] . 'دق';
            if ($hr[2]) {
                $return .= ' و' . $hr[2] . 'ث';
            }
        } elseif ($decode_format == 'ARABIC-TIME-WITHOUT-SECONDS') {
            $hr = $value;
            $hr = explode(':', $hr);

            $return =
                'س' . $hr[0] . ' و' . $hr[1] . 'دق';
        } else {
            $return = $value;
        }


        return $return;
    }

    public static function justDecodeValue($value, $structure)
    {
        if ($structure['TYPE'] == 'GDAT') {
            return self::displayGDate($value);
        } elseif ($structure['TYPE'] == 'DATE') {
            return self::displayDate($value);
        } else {
            return $value;
        }
    }

    public static function formatDateForDB($value)
    {
        $value_arr = explode('-', trim($value));
        //die("date value [$value] exploded to ".var_export($value_arr,true)."count = ".count($value_arr));
        if (count($value_arr) == 3) {
            $return = $value_arr[0] . $value_arr[1] . $value_arr[2];
            //die("date value [$value] exploded to ".var_export($value_arr,true)."count = ".count($value_arr)."value so = [$value]");
        } else {
            $return = $value;
        }

        return $return;
    }

    public static function formatGDateForDB($value)
    {
        $value_arr = explode('/', trim($value));
        if (count($value_arr) == 3) {
            $return = $value_arr[2] . '-' . $value_arr[0] . '-' . $value_arr[1];
        } else {
            $return = $value;
        }

        return $return;
    }

    public static function displayGDate($val)
    {
        list($val,) = explode(" ", $val);

        if (strlen($val) == 10) {
            list($yyyy, $mm, $dd) = explode('-', $val);
            return "$mm/$dd/$yyyy";
        }

        return $val;
    }

    public static function displayGDateLong($val)
    {
        $date_en = substr($val, 0, 10);
        $date_en = explode('-', $date_en);

        $tmstmp = mktime(
            0,
            0,
            0,
            $date_en[1],
            $date_en[2],
            $date_en[0]
        );

        // Monday 8th of August 2005
        return self::nameDayTranslate(date('l', $tmstmp)) . ' ' . date('j', $tmstmp) . ' ' . self::nameMonthTranslate(date('F', $tmstmp)) . ' ' . date('Y', $tmstmp);
    }


    public static function displayDate($val)
    {
        if (strlen($val) == 8) {
            $yyyy = substr($val, 0, 4);
            $mm = substr($val, 4, 2);
            $dd = substr($val, 6, 2);
            return "$yyyy-$mm-$dd";
        }

        return $val;
    }

    public static function addPeriodToGregDate($nb_days, $nb_months = 0, $nb_years = 0, $from_date = '')
    {
        if (!$from_date) $from_date = date('Y-m-d');
        //echo "<br>from_date = $from_date";
        $from_tab = explode('-', $from_date);
        //echo "<br>from_tab = ".var_export($from_tab,true);


        $to_date = date("Y-m-d", mktime(0, 0, 0, intval($from_tab[1]) + $nb_months, intval($from_tab[2]) + $nb_days, intval($from_tab[0]) + $nb_years));

        //echo "<br>from_date + $nb_days = $to_date";

        return ($to_date);
    }

    public static function addXDaysToGregDate($nb_days, $from_date = '')
    {
        return self::addPeriodToGregDate($nb_days, 0, 0, $from_date);
    }
}
