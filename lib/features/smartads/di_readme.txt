// Extensions to DI container - added smartads registrations

import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/ad_admin_repository.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/repositories/fcm_repository.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/ad_admin_service.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/services/fcm_service.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/controllers/ad_admin_controller.dart';
import 'package:flutter_sixvalley_ecommerce/features/smartads/domain/models/device_token_model.dart';

// NOTE: This file is an augmentation to the existing di_container. To apply these registrations
// the main di_container.dart was updated in the branch to include them.
