<?php
	
    $servername = "db-4jmpf.pub-cdb.ntruss.com";
    $username = "swc4th";
//     $password = "swc4ck!@#$";
    $password = "swc4ck!@#";
    $dbname = "swc_base";
//     $table = "Employees"; // lets create a table named Employees.
 
    // we will get actions from the app to do operations in the database...
    
    // http://local-api.wbcm.co.kr/membership/membershipGradeInfoList?USERID=money833n&SPT_LVL=ILNS13100001&CUL_LVL=ILAC14020001&SPT_STATUS_CD=200&CUL_STATUS_CD=200&ML_MEMKIND_CD=EMP&ML_GUBUN_CD=NOR&IC_CARD_GUBUN_CD=CUL
$conn = new mysqli($servername, $username, $password, $dbname);
	echo $conn->connect_error;exit;
    
    $USERID = (isset($_GET["USERID"]) && $_GET["USERID"] != "") ? $_GET["USERID"] : "";
    $SPT_LVL = (isset($_GET["SPT_LVL"]) && $_GET["SPT_LVL"] != "") ? $_GET["SPT_LVL"] : "";
    $CUL_LVL = (isset($_GET["CUL_LVL"]) && $_GET["CUL_LVL"] != "") ? $_GET["CUL_LVL"] : "";
    $SPT_STATUS_CD = (isset($_GET["SPT_STATUS_CD"]) || $_GET["SPT_STATUS_CD"]=="null") ? $_GET["SPT_STATUS_CD"] : "";
    $CUL_STATUS_CD = (isset($_GET["CUL_STATUS_CD"]) || $_GET["CUL_STATUS_CD"]=="null") ? $_GET["CUL_STATUS_CD"] : "";
    $IC_CARD_GUBUN_CD = (isset($_GET["IC_CARD_GUBUN_CD"]) && $_GET["IC_CARD_GUBUN_CD"] != "" ) ? $_GET["IC_CARD_GUBUN_CD"] : "CUL";
    
    $ML_MEMKIND_CD = (isset($_GET["ML_MEMKIND_CD"]) && $_GET["ML_MEMKIND_CD"] != "") ? $_GET["ML_MEMKIND_CD"] : "EMP";
    $ML_GUBUN_CD = (isset($_GET["ML_GUBUN_CD"]) && $_GET["ML_GUBUN_CD"] != "") ? $_GET["ML_GUBUN_CD"] : "NOR";
    $IC_LVL_CD = (isset($_GET["IC_LVL_CD"]) || $_GET["IC_LVL_CD"]=="null") ? $_GET["IC_LVL_CD"] : "";
    
    
    // Create Connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check Connection
    if($conn->connect_error){
        die("Connection Failed: " . $conn->connect_error);
        return;
    }
	
    // 선택등급목록
    $response = curlGet('/membership/membershipGradeInfoSelectList', Array(
    		"USERID" => $USERID
    		, "SPT_LVL" => $SPT_LVL
    		, "CUL_LVL" => $CUL_LVL
    		, "SPT_STATUS_CD" => $SPT_STATUS_CD
    		, "CUL_STATUS_CD" => $CUL_STATUS_CD
    		, "ML_MEMKIND_CD" => $ML_MEMKIND_CD
    		, "ML_GUBUN_CD" => $ML_GUBUN_CD
    		, "IC_CARD_GUBUN_CD" => 'CUL'
    		, "IC_LVL_CD"=>$IC_LVL_CD
    		));
    
    
    if(!isset($response['response']['data'])){
    	return $this->outputResult(400, '선택 컬처멤버십 등급안내 정보를 가져오는 중에 오류가 발생하였습니다','');
    }
    
    $membershipGradeInfoSelectList = $response['response']['data']['membershipGradeInfoSelectList'][0];//선택 컬처멤버십 등급정보
    $membership_info_ver2 = membership_info_ver2();
    $membershipInfo = $membership_info_ver2['CUL'];
    
    $response = curlGet('/membership/membershipGradeInfoList', array(
    		'USERID' => $USERID,
    		'SPT_LVL' => $SPT_LVL,
    		'CUL_LVL' => $CUL_LVL,
    		'SPT_STATUS_CD' => $SPT_STATUS_CD,
    		'CUL_STATUS_CD' => $CUL_STATUS_CD,
    		'ML_MEMKIND_CD' => $ML_MEMKIND_CD,
    		'ML_GUBUN_CD' => $ML_GUBUN_CD,
    		'IC_CARD_GUBUN_CD' => $IC_CARD_GUBUN_CD,
    ));
	
	$aViewData['status'] = $response['status'];
    $aViewData['message'] = $response['response']['message'];
    $aViewData['data'] = $membershipGradeInfoSelectList;
    $aViewData['data']['membershipGradeInfoList'] = $response['response']['data']['membershipGradeInfoList'];//전체 컬처멤버십 등급정보(가격,횟수등등DB)
    
    $aViewData['data']['membershipInfo'] = $membershipInfo;//전체 컬처멤버십 아이콘 및 상세컬처정보(public_controller DB(x))
     
    $aViewData['data']['benefitInfo'] = $membershipInfo[$membershipGradeInfoSelectList['IC_LVL_CD']]['benefit'];//선택 컬처멤버십 아이콘 및 상세컬처정보
	$aViewData['data']['caution_info'] = (isset($membershipInfo[$membershipGradeInfoSelectList['IC_LVL_CD']]['caution_info']))? $membershipInfo[$membershipGradeInfoSelectList['IC_LVL_CD']]['caution_info']: "";
	
    header('Content-Type: application/json');
    // tell browser that its a json data
    echo json_encode($aViewData);
    exit;
    
    
    /**
	 * 일반 타입의 Get Method  
	 * @param string $url
	 * @param array $paramArr
	 * @return string|array json type 의 문자열 결과값 
	 */
	function curlGet($url, $paramArr)
	{
	    $curl = curl_init();
	    $response = array();
	    $httpcode = 503;
	    try {
	        
	        curl_setopt_array($curl, array(
	            CURLOPT_URL => "https://api.wbcm.co.kr" . $url . arrayToParameters($paramArr),
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_ENCODING => "",
	            CURLOPT_MAXREDIRS => 10,
	            CURLOPT_TIMEOUT => 30,
	            CURLOPT_FOLLOWLOCATION => false,
	            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	            CURLOPT_CUSTOMREQUEST => "GET"
	        ));
	
	        $response = curl_exec($curl);
	        $httpcode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
	                
	        $err = curl_error($curl);
	        
	    } catch (Exception $e) {
	        $err = $e;
	    } finally {
	        curl_close($curl);
	    }
	
	    if ($err) {
	        return makeResponse($httpcode, $err);
	    } else {
	        return makeResponse($httpcode, $response);
	    }
	}

    
    
    function makeResponse($httpcode, $response)
    {
    
    	if(gettype($response) == 'string'){
    		$response = json_decode($response, true);
    	}
    
    	return array(
    			'status' => $httpcode,
    			'response' => $response
    	);
    }
    
    
    /**
     * Key Value type의 array를 쿼리형 파라미터로 전환하여 줌.
     *
     * @param Array $paramArr
     * @return string
     */
    function arrayToParameters($paramArr)
    {
    	$paramStr = "?";
    	if (isset($paramArr) && is_array($paramArr)) {
    		foreach ($paramArr as $key => $val) {
    			$paramStr .= urlencode($key) . "=" . urlencode($val) . "&";
    		}
    	}
    	return $paramStr;
    }
    /**
     * 정상코드 출력
     */
    function outputResult($HTTP_RESULT, $msg, $data = array() ) {
    	$response = array (
    			"message" => $msg,
    			"data" => $data
    	);
    	echo json_encode($response, $HTTP_RESULT);
    }
    
    
    function membership_info_ver2(){
    
    	$aData = array(
    			"SPT" => Array()
    			, "CUL" => Array(
    					// 블루
    					"ILAC13100001" => Array(
    							"benefit" => Array(
    									Array(
    											"image" => "/images/icon/icon_movie.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 5,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국] : <font color='black'>월 1 회</font> </span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											, "desc2" => "· <span>카페[지점] : <font color='black'>월 2 회</font> <div style='padding-left:15px;color:red;'>(※베이커리[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 4,000원 할인<Br>- 이용시설 : 이용종목이 카페명[지점]으로 표기된 카페</div>"
    											//, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_windmill.png"
    											, "title" => "베이커리"
    											, "desc" => "· <span>베이커리[전국] : <font color='black'>월 1 회</font> </span><Br/><div style='padding-left:15px;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 파리바게뜨, 던킨도너츠</div>"
    											, "desc2" => "· <span>베이커리[지점] : <font color='black'>월 2 회</font> <div style='padding-left:15px;color:red;'>(※카페[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 3,500원 할인<Br>- 이용시설 : 이용종목이 베이커리명[지점]으로 표기된 베이커리"
    											//, "limit" => 1
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_coffee2.png"
    									// 												, "title" => "카페[지점] & 베이커리[지점] 통합"
    									// 												, "desc" => "제휴된 카페[지점] & 베이커리[지점] 최대 4,000원 할인"
    									// 												, "limit" => 2
    
    									// 												)
    									, Array(
    											"image" => "/images/icon/icon_coffee2.png"
    											, "title" => "이색카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_healing.png"
    											, "title" => "힐링카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam.png"
    											, "title" => "패밀리레스토랑"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											//, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger.png"
    											, "title" => "푸드[전국]"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam.png"
    											, "title" => "푸드[지점]"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "limit" => 2
    											)
    									,Array(
    											"image" => "/images/icon/icon_autobicycle.png"
    											, "title" => "배달"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_store.png"
    											, "title" => "편의점[전국]"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin.png"
    											, "title" => "피부/미용"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa.png"
    											, "title" => "사우나"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											// 												, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "limit" => 1
    											)
    									,Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "limit" => 3
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									// 												, "etcCode"=>Array(
    									// 														'ILAC17080001'
    									// 														)//제주올레
    									// 												)
    									),
    							"caution_info"=>'※ 위 종목 외 <span style="font-weight:bold;">병원, 숙박, 여행, 테마파크</span> 등 이용 가능'
    							,"icon_alphbet"=>'B'
    							)
    					// 실버
    					, "ILAC13100002" => Array(
    							"benefit" => Array(
    									Array(
    											"image" => "/images/icon/icon_movie.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 5,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국] : <font color='black'>월 1 회</font> </span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											, "desc2" => "· <span>카페[지점] : <font color='black'>월 3 회</font><div style='padding-left:15px;color:red;'>(※베이커리[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 4,000원 할인<Br>- 이용시설 : 이용종목이 카페명[지점]으로 표기된 카페</div>"
    											//, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_windmill.png"
    											, "title" => "베이커리"
    											, "desc" => "· <span>베이커리[전국] : <font color='black'>월 1 회</font> </span><Br/><div style='padding-left:15px;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 파리바게뜨, 던킨도너츠</div>"
    											, "desc2" => "· <span>베이커리[지점] : <font color='black'>월 3 회</font><div style='padding-left:15px;color:red;'>(※카페[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 3,500원 할인<Br>- 이용시설 : 이용종목이 베이커리명[지점]으로 표기된 베이커리"
    											//, "limit" => 2
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_coffee2.png"
    									// 												, "title" => "카페[지점] & 베이커리[지점] 통합"
    									// 												, "desc" => "제휴된 카페[지점] & 베이커리[지점] 최대 4,000원 할인"
    									// 												, "limit" => 3
    									// 												)
    									, Array(
    											"image" => "/images/icon/icon_coffee2.png"
    											, "title" => "이색카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "limit" => 3
    											)
    									, Array(
    											"image" => "/images/icon/icon_healing.png"
    											, "title" => "힐링카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam.png"
    											, "title" => "패밀리레스토랑"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger.png"
    											, "title" => "푸드[전국]"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam.png"
    											, "title" => "푸드[지점]"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "limit" => 3
    											)
    									, Array(
    											"image" => "/images/icon/icon_autobicycle.png"
    											, "title" => "배달"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_store.png"
    											, "title" => "편의점[전국]"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin.png"
    											, "title" => "피부/미용"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa.png"
    											, "title" => "사우나"
    											// 												, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "limit" => 2
    											)
    									,Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "limit" => 3
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									// 												, "etcCode"=>Array(
    									// 														'ILAC17080001'
    									// 														)//제주올레
    									// 												)
    									),
    							"caution_info"=>'※ 위 종목 외 <span style="font-weight:bold;">병원, 숙박, 여행, 테마파크</span> 등 이용 가능'
    							,"icon_alphbet"=>'S'
    							)
    					// 골드
    					, "ILAC13100003" => Array(
    							"benefit" => Array(
    									Array(
    											"image" => "/images/icon/icon_movie.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 10,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국] : <font color='black'>월 2 회</font> </span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											, "desc2" => "· <span>카페[지점] : <font color='black'>월 5 회</font><div style='padding-left:15px;color:red;'>(※베이커리[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 4,000원 할인<Br>- 이용시설 : 이용종목이 카페명[지점]으로 표기된 카페</div>"
    											//, "desc" => "투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티 최대 4,800원 할인"
    											//, "limit" => 3
    											)
    									, Array(
    											"image" => "/images/icon/icon_windmill.png"
    											, "title" => "베이커리"
    											, "desc" => "· <span>베이커리[전국] : <font color='black'>월 2 회</font> </span><Br/><div style='padding-left:15px;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 파리바게뜨, 던킨도너츠</div>"
    											, "desc2" => "· <span>베이커리[지점] : <font color='black'>월 5 회</font><div style='padding-left:15px;color:red;'>(※카페[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 3,500원 할인<Br>- 이용시설 : 이용종목이 베이커리명[지점]으로 표기된 베이커리"
    											//, "desc" => "파리바게뜨, 던킨도너츠 최대 3,500원 할인"
    											//, "limit" => 3
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_coffee2.png"
    									// 												, "title" => "카페[지점] & 베이커리[지점] 통합"
    									// 												, "desc" => "제휴된 카페[지점] & 베이커리[지점] 최대 4,000원 할인"
    									// 												, "limit" => 5
    									// 												)
    									, Array(
    											"image" => "/images/icon/icon_coffee2.png"
    											, "title" => "이색카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "limit" => 5
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2.png"
    											, "title" => "힐링카페"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											, "limit" => 3
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam.png"
    											, "title" => "패밀리레스토랑"
    											//, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "limit" => 3
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger.png"
    											, "title" => "푸드[전국]"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam.png"
    											, "title" => "푸드[지점]"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "limit" => 5
    											)
    									, Array(
    											"image" => "/images/icon/icon_autobicycle.png"
    											, "title" => "배달"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_store.png"
    											, "title" => "편의점[전국]"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin.png"
    											, "title" => "피부/미용"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "limit" => 3
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa.png"
    											, "title" => "사우나"
    											// 												, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "limit" => 3
    											)
    									,Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "limit" => 3
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									// 												, "etcCode"=>Array(
    									// 														'ILAC17080001'
    									// 														)//제주올레
    									// 												)
    									),
    							"caution_info"=>'※ 위 종목 외 <span style="font-weight:bold;">병원, 숙박, 여행, 테마파크</span> 등 이용 가능'
    							,"icon_alphbet"=>'G'
    							)
    					// 빅2
    					// 						, "ILAC16060001" => Array(
    					// 								"benefit" => Array(
    					// 										Array(
    					// 												"image" => "/images/icon/icon_vips.png"
    					// 												, "title" => "빕스"
    					// 												, "desc" => "빕스10,000원 할인+제휴카드30%할인"
    					// 												, "limit" => 3
    					// 												)
    					// // 										, Array(
    					// // 												"image" => "/images/icon/icon_outback.png"
    					// // 												, "title" => "아웃백"
    					// // 												, "desc" => "아웃백10,000원 할인+제휴카드50%할인"
    					// // 												, "limit" => 3
    					// // 												)
    					// 										, Array(
    					// 												"image" => "/images/icon/icon_coffee.png"
    					// 												, "title" => "카페/베이커리"
    					// 												, "desc" => "최대 4,000원 할인 (아메리카노 무료)"
    					// 												, "limit" => 1
    					// 												)
    					// 										)
    					// 								)
    					// 올레
    					, "ILAC17080001" => Array(
    							"benefit" => Array(
    									Array(
    											"image" => "/images/icon/icon_movie.png"
    											, "title" => "영화"
    											, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페[지점] & 베이커리[지점] 통합"
    											, "desc" => "제주도 내 제휴된 카페[지점] & 베이커리[지점] 최대 4,000원 할인"
    											, "limit" => 7
    											)
    
    									, Array(
    											"image" => "/images/icon/icon_healing.png"
    											, "title" => "힐링카페"
    											//, "desc" => "제주도 내 미스터힐링 8,000원(60%) 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											, "limit" => 7
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa.png"
    											, "title" => "사우나"
    											// 												, "desc" => "제주도 내 제휴된 사우나 무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "limit" => 7
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "limit" => 3
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									, Array(
    											"image" => "/images/icon/icon_movie_red.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 5,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee_red.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											, "desc2" => "· <span>카페[지점]</span><div style='padding-left:15px;'> - 이용혜택 : 최대 4,000원 할인<Br>- 이용시설 : 이용종목이 카페명[지점]으로 표기된 카페</div>"
    											//, "desc" => "투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티 최대 4,800원 할인"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110002','ILAC21110003','ILAC21110001','ILAC21110004'
    													)//블루등급,실버등급,골드등급,올-카페,올-카페2,딜리버리 등급,영&뷰티등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "이색카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "패밀리레스토랑"
    											// 												, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger_red.png"
    											, "title" => "푸드[전국]"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "푸드[지점]"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_autobicycle_red.png"
    											, "title" => "배달"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_store_red.png"
    											, "title" => "편의점[전국]"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110004'
    													)//블루등급,실버등급,골드등급,영&뷰티
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "피부/미용"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									),
    							"caution_info"=>'※ 위 종목 외 <span style="font-weight:bold;">병원, 숙박, 여행, 테마파크</span> 등 이용 가능'
    							,"icon_alphbet"=>'J'
    							)
    
    					// 스윗매니아1 : 한도 7로 변경 2021.09.01 예정
    					, "ILAC19110001" => Array(
    							"benefit" => Array(
    									// 										Array(
    									// 												"image" => "/images/icon/icon_coffee.png"
    									// 												, "title" => "카페[지점] & 베이커리[지점] 통합"
    									// 												, "desc" => "제휴된 카페[지점] & 베이커리[지점] 최대 4,000원 할인"
    									// 												, "limit" => 7
    									// 												)
    									Array(
    											"image" => "/images/icon/icon_windmill.png"
    											, "title" => "베이커리"
    											, "desc" => "· <span>베이커리[전국] : <font color='black'>월 5 회</font> </span><Br/><div style='padding-left:15px;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 파리바게뜨, 던킨도너츠</div>"
    											, "desc2" => "· <span>베이커리[지점] : <font color='black'>월 7 회</font><div style='padding-left:15px;color:red;'>(※카페[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 3,500원 할인<Br>- 이용시설 : 이용종목이 베이커리명[지점]으로 표기된 베이커리"
    											//, "desc" => "전국 파리바게뜨, 던킨도너츠 최대 3,500원 할인"
    											//, "limit" => 5
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[지점] : <font color='black'>월 7 회</font> <div style='padding-left:15px;color:red;'>(※베이커리[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 4,000원 할인<Br>- 이용시설 : 이용종목이 카페명[지점]으로 표기된 카페</div>"
    											//, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "limit" => 3
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									// 												, "etcCode"=>Array(
    									// 														'ILAC17080001'
    									// 														)//제주올레
    									// 												)
    									, Array(
    											"image" => "/images/icon/icon_movie_red.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 10,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee_red.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											//, "desc2" => "· <span>카페[지점]</span><div style='padding-left:15px;'> - 이용혜택 : 최대 4,000원 할인<Br>- 이용시설 : 이용종목이 카페명[지점]으로 표기된 카페</div>"
    											//, "desc" => "투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티 최대 4,800원 할인"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110002','ILAC21110003','ILAC21110001','ILAC21110004'
    													)//블루등급,실버등급,골드등급,올-카페,올-카페2,딜리버리 등급,영&뷰티등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "이색카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "힐링카페"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "패밀리레스토랑"
    											// 												, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger_red.png"
    											, "title" => "푸드[전국]"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "푸드[지점]"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_autobicycle_red.png"
    											, "title" => "배달"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_store_red.png"
    											, "title" => "편의점[전국]"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110004'
    													)//블루등급,실버등급,골드등급,영&뷰티
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "피부/미용"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa_red.png"
    											, "title" => "사우나"
    											// 												, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									),
    							"caution_info"=>'※ 제휴된 <span style="font-weight:bold;">하얀풍차, 한스케잌, 뚜주루</span> 등 인기 로컬 베이커리[지점] 최대 4,500원 할인 이용 가능'
    							,"icon_alphbet"=>'S1'
    							)
    					// 스윗매니아2
    					, "ILAC21080001" => Array(
    							"benefit" => Array(
    									//         				        		Array(
    									// 	        				        			"image" => "/images/icon/icon_coffee.png"
    									// 	        				        			, "title" => "카페[지점] & 베이커리[지점] 통합"
    									// 	        				        			, "desc" => "제휴된 카페[지점] & 베이커리[지점] 최대 4,000원 할인"
    									// 	        				        			, "limit" => 15
    									// 	        				        		)
    									Array(
    											"image" => "/images/icon/icon_windmill.png"
    											, "title" => "베이커리"
    											, "desc" => "· <span>베이커리[전국] : <font color='black'>월 10 회</font> </span><Br/><div style='padding-left:15px;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 파리바게뜨, 던킨도너츠</div>"
    											, "desc2" => "· <span>베이커리[지점] : <font color='black'>월 15 회</font><div style='padding-left:15px;color:red;'>(※카페[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 3,500원 할인<Br>- 이용시설 : 이용종목이 베이커리명[지점]으로 표기된 베이커리"
    											//, "desc" => "파리바게뜨, 던킨도너츠 최대 3,500원 할인"
    											//, "limit" => 10
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[지점] : <font color='black'>월 15 회</font> <div style='padding-left:15px;color:red;'>(※베이커리[지점] 한도와 통합 한도로 적용)</div></span><div style='padding-left:15px;'> - 이용혜택 : 최대 4,000원 할인<Br>- 이용시설 : 이용종목이 카페명[지점]으로 표기된 카페</div>"
    											//, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											//         				        				, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									//         				        		, Array(
    									//         				        				"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									//         				        				, "etcCode"=>Array(
    									//         				        						'ILAC17080001'
    									//         				        						)//제주올레
    									//         				        				)
    									, Array(
    											"image" => "/images/icon/icon_movie_red.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 10,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee_red.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											//, "desc2" => "· <span>카페[지점]</span><div style='padding-left:15px;'> - 이용혜택 : 최대 4,000원 할인<Br>- 이용시설 : 이용종목이 카페명[지점]으로 표기된 카페</div>"
    											//, "desc" => "투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티 최대 4,800원 할인"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110002','ILAC21110003','ILAC21110001','ILAC21110004'
    													)//블루등급,실버등급,골드등급,올-카페,올-카페2,딜리버리 등급,영&뷰티등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "이색카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "힐링카페"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "패밀리레스토랑"
    											//         				        				, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger_red.png"
    											, "title" => "푸드[전국]"
    											//         				        				, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "푸드[지점]"
    											//         				        				, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_autobicycle_red.png"
    											, "title" => "배달"
    											//         				        				, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_store_red.png"
    											, "title" => "편의점[전국]"
    											//         				        				, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110004'
    													)//블루등급,실버등급,골드등급,영&뷰티
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "피부/미용"
    											//         				        				, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa_red.png"
    											, "title" => "사우나"
    											//         				        				, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    
    									),
    							"caution_info"=>'※ 제휴된 <span style="font-weight:bold;">하얀풍차, 한스케잌, 뚜주루</span> 등 인기 로컬 베이커리[지점] 최대 4,500원 할인 이용 가능'
    							,"icon_alphbet"=>'S2'
    							)
    					// 올-카페 등급
    					, "ILAC21110002" => Array(
    							"benefit" => Array(
    									Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											//, "desc" => "투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티 최대 4,800원 할인"
    											, "limit" => 3
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "limit" => 3
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									// 												, "etcCode"=>Array(
    									// 														'ILAC17080001'
    									// 														)//제주올레
    									// 												)
    									, Array(
    											"image" => "/images/icon/icon_movie_red.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 10,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "이색카페"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "힐링카페"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "패밀리레스토랑"
    											// 												, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger_red.png"
    											, "title" => "푸드[전국]"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "푸드[지점]"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_autobicycle_red.png"
    											, "title" => "배달"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_store_red.png"
    											, "title" => "편의점[전국]"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110004'
    													)//블루등급,실버등급,골드등급,영&뷰티
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "피부/미용"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa_red.png"
    											, "title" => "사우나"
    											// 												, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									)
    							,"icon_alphbet"=>'A1'
    
    							)
    					// 올-카페2 등급
    					, "ILAC21110003" => Array(
    							"benefit" => Array(
    									Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											//, "desc" => "투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티 최대 4,800원 할인"
    											, "limit" => 7
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "limit" => 3
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									// 												, "etcCode"=>Array(
    									// 														'ILAC17080001'
    									// 														)//제주올레
    									// 												)
    									, Array(
    											"image" => "/images/icon/icon_movie_red.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 10,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "이색카페"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "힐링카페"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "패밀리레스토랑"
    											// 												, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger_red.png"
    											, "title" => "푸드[전국]"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "푸드[지점]"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_autobicycle_red.png"
    											, "title" => "배달"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_store_red.png"
    											, "title" => "편의점[전국]"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110004'
    													)//블루등급,실버등급,골드등급,영&뷰티
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "피부/미용"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa_red.png"
    											, "title" => "사우나"
    											// 												, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									)
    							,"icon_alphbet"=>'A2'
			
    							)
    					// 딜리버리 등급
    					, "ILAC21110001" => Array(
    							"benefit" => Array(
    									Array(
    											"image" => "/images/icon/icon_autobicycle.png"
    											, "title" => "배달"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "limit" => 7
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											//, "desc" => "투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티 최대 4,800원 할인"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_windmill.png"
    											, "title" => "베이커리"
    											, "desc" => "· <span>베이커리[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 파리바게뜨, 던킨도너츠</div>"
    											//, "desc" => "파리바게뜨, 던킨도너츠 최대 3,500원 할인"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger.png"
    											, "title" => "푸드[전국]"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											, "limit" => 2
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "뷰티스토어[전국]"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "limit" => 3
    											, "etcCode"=>Array(
    													'ILAC21110004'
    													)//영&뷰티
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									// 												, "etcCode"=>Array(
    									// 														'ILAC17080001'
    									// 														)//제주올레
    									// 												)
    									, Array(
    											"image" => "/images/icon/icon_movie_red.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 10,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "이색카페"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "힐링카페"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "패밀리레스토랑"
    											// 												, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "푸드[지점]"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_store_red.png"
    											, "title" => "편의점[전국]"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110004'
    													)//블루등급,실버등급,골드등급,영&뷰티
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "피부/미용"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa_red.png"
    											, "title" => "사우나"
    											// 												, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									)
    							,"icon_alphbet"=>'D'
    							)
    					// 영&뷰티 등급
    					, "ILAC21110004" => Array(
    							"benefit" => Array(
    									Array(
    											"image" => "/images/icon/icon_skin.png"
    											, "title" => "뷰티스토어[전국]"
    											// 												, "desc" => "토니모리 최대 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 5,000원 할인 (※추가이용료 2,000원 즉시 결제 필요)<Br>- 이용시설 : 전국 올리브영</div>"
    											, "limit" => 3
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee.png"
    											, "title" => "카페"
    											, "desc" => "· <span>카페[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 4,800원 할인<Br>- 이용시설 : 투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티, 요거프레소, 쥬씨, 엔제리너스, 메가MGC커피</div>"
    											//, "desc" => "투썸플레이스, 이디야, 폴바셋, 공차, 배스킨라빈스, 빽다방, 더벤티 최대 4,800원 할인"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_windmill.png"
    											, "title" => "베이커리"
    											, "desc" => "· <span>베이커리[전국]</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 파리바게뜨, 던킨도너츠</div>"
    											//, "desc" => "파리바게뜨, 던킨도너츠 최대 3,500원 할인"
    											, "limit" => 1
    											)
    									, Array(
    											"image" => "/images/icon/icon_store.png"
    											, "title" => "편의점[전국]"
    											// 												, "desc" => "CU, GS25 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 전국 CU, GS25</div>"
    											, "limit" => 1
    											)
    									// 										, Array(
    									// 												"image" => "/images/icon/icon_movie.png"
    									// 												, "title" => "영화"
    									// 												, "desc" => "제주도 내 제휴된 영화관 최대 9,000원 할인"
    									// 												, "etcCode"=>Array(
    									// 														'ILAC17080001'
    									// 														)//제주올레
    									// 												)
    									, Array(
    											"image" => "/images/icon/icon_movie_red.png"
    											, "title" => "영화 & 공연"
    											, "desc" => "· <span>영화</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 9,000원 할인<Br> - 이용시설 : 롯데시네마 전 지점, 제휴된 메가박스 지점</div>"
    											, "desc2" => "· <span>공연</span><Br/><div style='padding-left:15px;'> - 이용혜택 : 최대 80% 기본 할인 + 최대 10,000원 추가 할인<br>- 이용시설 : 클립서비스(공연 예매)</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "이색카페"
    											//, "desc" => "키즈카페, 만화카페, 낚시카페 등 최대 무료 이용"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 무료 이용<Br>- 이용시설 : 제휴된 키즈카페, 만화카페, 낚시카페</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_coffee2_red.png"
    											, "title" => "힐링카페"
    											//, "desc" => "미스터힐링 8,000원(60%) 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 8,000원(60%) 할인<Br>- 이용시설 : 전국 미스터힐링</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "패밀리레스토랑"
    											// 												, "desc" => "제휴된 빕스[지점] 10,000원 할인(※제휴카드 중복 할인 가능)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 10,000원 할인(※제휴카드 중복 할인 가능)<Br>- 이용시설 : 제휴된 빕스 지점</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_hamburger_red.png"
    											, "title" => "푸드[전국]"
    											// 												, "desc" => "맥도날드, 롯데리아, 이삭토스트 최대 5,100원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 5,100원 할인<Br>- 이용시설 : 전국 맥도날드, 롯데리아, 이삭토스트, 바르다김선생, 샐러디</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_fam_red.png"
    											, "title" => "푸드[지점]"
    											// 												, "desc" => "제휴된 푸드스토어 최대 6,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 6,000원 할인<Br>- 이용시설 : 제휴된 푸드스토어</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_autobicycle_red.png"
    											, "title" => "배달"
    											// 												, "desc" => "요기요 3,000원 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 3,000원 할인<Br>- 이용시설 : 요기요</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003','ILAC21110001'
    													)//블루등급,실버등급,골드등급,딜리버리등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_skin_red.png"
    											, "title" => "피부/미용"
    											// 												, "desc" => "헤어샵, 네일샵, 피부관리샵 등 최대 70% 할인"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 최대 70% 할인<Br>- 이용시설 : 제휴된 헤어샵, 네일샵, 피부관리샵</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    									, Array(
    											"image" => "/images/icon/icon_spa_red.png"
    											, "title" => "사우나"
    											// 												, "desc" => "무료 이용(※일부 시설 추가이용료 발생)"
    											, "desc" => "<div style='padding-left:15px;float:left;'> - 이용혜택 : 무료 이용(※일부 시설 추가이용료 발생)<Br>- 이용시설 : 제휴된 사우나</div>"
    											, "etcCode"=>Array(
    													'ILAC13100001','ILAC13100002','ILAC13100003'
    													)//블루등급,실버등급,골드등급
    											)
    
    									)
    							,"icon_alphbet"=>'Y'
    							)
    
    
    					)
    
    	);
    
    	return $aData;
    }
?>
