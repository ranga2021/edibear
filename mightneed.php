 <!---------
                            <div class='col-md-8'>
                                <div class='row justify-content-center'>";

                                
                                

                                     foreach ( $user->fetchAll(array("image"),array("testimonials_images"),array("testimonial_id"=>$testiArr['id'])) as $testiImages ) {
                                        $image = $this->createCachelessImage("$adminImageDot./img/testimonials/".$testiImages['image']);
                                        $html .= "
                                            <div class='col-4 p-2 text-center'>
                                                <img src='$image' style='max-width:100%; max-height:150px;' alt='Testimonial Image'>
                                            </div>
                                        ";
                                    } 
                                    
                                    
        $html .= "              </div>
                            </div> 

                            -------->




                                    
                           







